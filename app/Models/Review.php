<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $review
 * @property bool $rating
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $book_id
 * @property int|null $books_count
 *
 * @property-read \App\Models\Book|null $book
 *
 * @method static \Database\Factories\ReviewFactory<self> factory($count = null, $state = [])
 */
class Review extends Model
{
    use HasFactory;

    protected $fillable = ['review', 'rating'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
