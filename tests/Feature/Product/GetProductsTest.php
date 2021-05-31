<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetProductsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_all_products()
    {
        Product::factory()->createMany([
            ['title' => 'Product one'],
            ['title' => 'Product two'],
        ]);

        $this->get(route('products.index'))
            ->assertJsonFragment([
                'title' => 'Product one',
                'title' => 'Product two',
            ]);
    }

    /** @test */
    public function get_product()
    {
        $product = Product::factory()->create(['title' => 'Product one']);

        $this->get(route('products.show', compact('product')))
            ->assertJsonFragment(['title' => 'Product one']);
    }
}
