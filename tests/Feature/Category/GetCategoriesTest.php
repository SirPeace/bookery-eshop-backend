<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetCategoriesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_all_categories()
    {
        $booksCategory = Category::factory()->create([
            'slug' => 'books',
            'title' => 'Books',
        ]);
        $ebooksCategory = Category::factory()->create([
            'title' => 'E-books',
            'slug' => 'e-books',
            'parent_id' => $booksCategory->id
        ]);
        $paperBooksCategory = Category::factory()->create([
            'title' => 'Paper books',
            'slug' => 'paper-books',
            'parent_id' => $booksCategory->id
        ]);

        $this->getJson(route('categories.index'))
            ->assertSuccessful()
            ->assertJson([
                "status" => "success",
                "data" => [
                    "categories" => [/* JSON fragments here */]
                ]
            ])
            ->assertJsonFragment($booksCategory->toArray())
            ->assertJsonFragment($ebooksCategory->toArray())
            ->assertJsonFragment($paperBooksCategory->toArray());
    }

    /** @test */
    public function get_single_category()
    {
        $category = Category::factory()->create();

        $this->getJson(route('categories.show', ['category' => $category->id]))
            ->assertSuccessful()
            ->assertJson([
                "status" => "success",
                "data" => [
                    "category" => $category->toArray()
                ]
            ]);
    }
}
