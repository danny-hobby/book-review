<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $title = $request->input('title');
        $filter = $request->input('filter');

        $books = Book::when(
            $title,
            fn($query, $title) =>
            $query->title($title)
        );

        $books = match ($filter) {
            'popular_last_month' => $books->popularLastMonth(),
            'popular_last_6months' => $books->popularLastSixMonths(),
            'highest_rated_last_month' => $books->highestRatedLastMonth(),
            'highest_rated_last_6months' => $books->highestRatedLastSixMonths(),
            default => $books->latest()->withAvgRating()->withReviewsCount()
        };

        $page = $request->has('page') ? $request->query('page') : 1;
        $cache_key = 'books:' . $page . ':' . $filter . ':' . $title;

        $books = Cache::remember($cache_key, 3600, fn() => $books->paginate());

        return view('books.index', ['books' => $books]);
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        $cache_key = 'book:' . $id;

        $book = Cache::remember(
            $cache_key,
            3600,
            fn() =>
            Book::with([
                'reviews' => fn($query) => $query->latest()
            ])
                ->withAvgRating()
                ->withReviewsCount()
                ->findOrFail($id)
        );

        $reviews = Cache::remember($cache_key . ':page:' . request('page', 1), 3600, function () use ($book) {
            return $book->reviews()->latest()->paginate(5);
        });

        return view('books.show', ['book' => $book, 'reviews' => $reviews]);
    }
}
