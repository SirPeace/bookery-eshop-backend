<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;

    public function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()
            ->for(Category::factory(), 'parent')
            ->create();
    }

    /** @test */
    public function order_belongs_to_parent_order()
    {
        $this->assertInstanceOf(
            Category::class,
            $this->category->parent
        );
    }
}
