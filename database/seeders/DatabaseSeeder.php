<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Book::factory(33)->create()->each(function ($book) {
            $num_of_reviews = random_int(5, 30);

            Review::factory()->count($num_of_reviews)
                ->good()
                ->for($book)
                ->create();
        });
        Book::factory(33)->create()->each(function ($book) {
            $num_of_reviews = random_int(5, 30);

            Review::factory()->count($num_of_reviews)
                ->avg()
                ->for($book)
                ->create();
        });
        Book::factory(34)->create()->each(function ($book) {
            $num_of_reviews = random_int(5, 30);

            Review::factory()->count($num_of_reviews)
                ->bad()
                ->for($book)
                ->create();
        });
    }
}
