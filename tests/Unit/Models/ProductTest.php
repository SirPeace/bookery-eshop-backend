<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()
            ->for(ProductCategory::factory(), 'category')
            ->create();
    }

    /** @test */
    public function product_belongs_to_category()
    {
        $this->assertInstanceOf(
            ProductCategory::class,
            $this->product->category
        );
    }
}
