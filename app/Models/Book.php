<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $builder->withCount([
            'reviews' => fn(Builder $b) => $this->dateRangeBuilder($b, $from, $to)
        ])
            ->orderBy('reviews_count', 'desc');
    }

    public function scopeHighestRated(Builder $builder, $from = null, $to = null): Builder
    {
        return $builder->withAvg([
            'reviews' => fn(Builder $b) => $this->dateRangeBuilder($b, $from, $to)
        ], 'rating')
            ->orderBy('reviews_avg_rating', 'desc');
    }

    public function scopeMinReviews(Builder $builder, int $min_reviews): Builder
    {
        return $builder->having('reviews_count', '>=', $min_reviews);
    }

    public function scopeWithRecentReviews(Builder $builder, \Closure $interval): Builder
    {
        return $builder->whereHas(
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
