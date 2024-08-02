<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $title
 * @property string $author
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $reviews_count
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Review[] $reviews
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Book title(string $title)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Book popular($from = null, $to = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Book highestRated($from = null, $to = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Book minReviews(int $min_reviews)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Book withRecentReviews(\Closure $interval)
 * @method static \Database\Factories\BookFactory<self> factory($count = null, $state = [])
 */
class Book extends Model
{
    use HasFactory;

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeTitle(Builder $builder, string $title): Builder
    {
        return $builder->where('title', 'LIKE', '%' . $title . '%'); // ->toSql();
    }

    public function scopePopular(Builder $builder, $from = null, $to = null): Builder
    {
        return $builder
            ->withCount([
                'reviews' => fn(Builder $b) => $this->dateRangeBuilder($b, $from, $to)
            ])
            ->orderBy('reviews_count', 'desc');
    }

    public function scopePopularLastMonth(Builder $builder): Builder
    {
        return $builder
            ->popular(now()->subMonth(), now())
            ->highestRated(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopePopularLastSixMonths(Builder $builder): Builder
    {
        return $builder
            ->popular(now()->subMonths(6), now())
            ->highestRated(now()->subMonths(6), now())
            ->minReviews(5);
    }

    public function scopeHighestRated(Builder $builder, $from = null, $to = null): Builder
    {
        return $builder
            ->withAvg([
                'reviews' => fn(Builder $b) => $this->dateRangeBuilder($b, $from, $to)
            ], 'rating')
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeHighestRatedLastMonth(Builder $builder): Builder
    {
        return $builder
            ->highestRated(now()->subMonth(), now())
            ->popular(now()->subMonth(), now())
            ->minReviews(2);
    }

    public function scopeHighestRatedLastSixMonths(Builder $builder): Builder
    {
        return $builder
            ->highestRated(now()->subMonths(6), now())
            ->popular(now()->subMonths(6), now())
            ->minReviews(5);
    }

    public function scopeMinReviews(Builder $builder, int $min_reviews): Builder
    {
        return $builder->having('reviews_count', '>=', $min_reviews);
    }

    public function scopeWithRecentReviews(Builder $builder, \Closure $interval): Builder
    {
        return $builder
            ->whereHas(
                'reviews',
                function (Builder $q) use ($interval) {
                    $q->whereBetween(
                        'created_at',
                        [$interval(now()), now()]
                    );
                }
            );
    }

    private function dateRangeBuilder(Builder $builder, $from = null, $to = null)
    {
        if ($from && !$to) {
            $builder->where('created_at', '>=', $from);
        } elseif (!$from && $to) {
            $builder->where('created_at', '<=', $to);
        } elseif ($from && $to) {
            $builder->whereBetween('created_at', [$from, $to]);
        }
    }
}
