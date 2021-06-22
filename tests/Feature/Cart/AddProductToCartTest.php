<?php

namespace Tests\Feature\Cart;

use App\Cart;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddProductToCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function add_product_to_cart()
    {
        $this->actingAs(User::factory()->create());

        $cart = new Cart();
        $cart->clear();
        $cart->addProduct(Product::factory()->create());

        $product = Product::factory()->create();

        $this->postJson(route('cart.add_product', ['product' => $product->id]))
            ->assertJson([
                "status" => "success",
                "data" => [
                    "cart" => []
                ]
            ])
            ->assertJsonFragment($product->toArray())
            ->assertJsonCount(2, 'data.cart');

        $this->assertCount(2, $cart->getProducts());
    }

    /** @test */
    public function add_product_with_count_to_cart()
    {
        $this->actingAs(User::factory()->create());

        $cart = new Cart();
        $cart->clear();

        $product = Product::factory()->create();

        $this->postJson(
            route('cart.add_product', ['product' => $product->id]),
            ['count' => 2]
        )
            ->assertSuccessful();

        $this->assertEquals(
            2,
            $cart->getProducts()->find($product->id)->count
        );
    }
}
