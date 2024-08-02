@extends('layouts.app')

@section('content')
    <h1 class="mb-10 text-2xl">Add Review for {{ $book->title }}</h1>

    <form method="POST" action="{{ route('books.reviews.store', $book) }}">
        @csrf
        <div>
            <label for="review">Review</label>
            @error('review')
                <p class="text-red-600">{{ $message }}</p>
            @enderror
            <textarea name="review" id="review" class="input mb-4">{{ old('review') }}</textarea>
        </div>
        <div>
            <label for="rating">Rating</label>
            @error('rating')
                <p class="text-red-600">{{ $message }}</p>
            @enderror
            <select name="rating" id="rating" class="input mb-4">
                <option value="">Select a Rating</option>
                @for ($i = 1; $i <= 5; $i++)
                    <option {{ old('rating') == $i ? 'selected' : '' }} value="{{ $i }}">{{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        <button type="submit" class="btn">Add Review</button>
    </form>
@endsection
