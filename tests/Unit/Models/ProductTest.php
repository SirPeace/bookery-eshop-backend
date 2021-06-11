<?php

namespace Tests\Unit\Models;

use App\Models\Attribute;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Product $product;

    public function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()
            ->for(Category::factory())
            ->has(Attribute::factory())
            ->create();
    }

    /** @test */
    public function product_belongs_to_category()
    {
        $this->assertInstanceOf(
            Category::class,
            $this->product->category
        );
    }

    /** @test */
    public function belongs_to_many_attributes()
    {
        $this->assertContainsOnlyInstancesOf(
            Attribute::class,
            $this->product->attributes
        );
    }
}
