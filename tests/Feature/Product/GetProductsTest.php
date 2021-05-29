<?php

namespace Tests\Feature\Feature\Product;

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
}
