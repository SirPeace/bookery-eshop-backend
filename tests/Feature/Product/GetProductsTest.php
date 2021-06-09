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
        [$productOne, $productTwo] = Product::factory(2)->create();

        $this->get(route('products.index'))
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'products' => [
                        ['title' => $productOne->title],
                        ['title' => $productTwo->title]
                    ]
                ]
            ]);
    }

    /** @test */
    public function get_product()
    {
        $product = Product::factory()->create(['title' => 'Product one']);

        $this->get(route('products.show', compact('product')))
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'product' => [
                        'title' => 'Product one'
                    ]
                ]
            ]);
    }
}
