<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Category::factory()->createMany([
            [
                'id'          => 1,
                'slug'        => 'books',
                'title'       => 'Books',
                'keywords'    => json_encode(['books']),
                'description' => 'The best books are all avialable in Bookery'
            ],
            [
                'parent_id'   => 1,
                'slug'        => 'paper_books',
                'title'       => 'Paper books',
                'keywords'    => json_encode(['books', 'paper books']),
                'description' =>
                    'The best books in paper are all avialable in Bookery'
            ],
            [
                'parent_id'   => 1,
                'slug'        => 'audio_books',
                'title'       => 'Audio books',
                'keywords'    => json_encode(['books', 'audio books']),
                'description' =>
                    'The best audio books are all avialable in Bookery'
            ],
            [
                'parent_id'   => 1,
                'slug'        => 'e-books',
                'title'       => 'E-books',
                'keywords'    => json_encode(['books', 'e-books']),
                'description' =>
                    'The best e-books are all avialable in Bookery'
            ],
        ]);
    }
}
