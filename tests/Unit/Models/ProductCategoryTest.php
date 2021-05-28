<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProductCategory $category;

    public function setUp(): void
    {
        parent::setUp();

        $this->category = ProductCategory::factory()
            ->for(ProductCategory::factory(), 'parent')
            ->create();
    }

    /** @test */
    public function order_belongs_to_parent_order()
    {
        $this->assertInstanceOf(
            ProductCategory::class,
            $this->category->parent
        );
    }
}
