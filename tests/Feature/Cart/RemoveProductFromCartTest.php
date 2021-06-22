<?php

namespace Tests\Feature;

use App\Cart;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RemoveProductFromCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function remove_product_from_cart()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();
        $cart = new Cart();
        $cart->clear();
        $cart->addProduct($product);
        $cart->addProduct(Product::factory()->create());
        $product->count = 1;

        $this->deleteJson(route('cart.remove_product', compact('product')))
            ->assertJson([
                "status" => "success",
                "data" => [
                    "cart" => []
                ]
            ])
            ->assertJsonMissing($product->toArray(), true)
            ->assertJsonCount(1, 'data.cart');

        $cart->refresh();

        $this->assertNull($cart->getProducts()->find($product->id));
    }

    /** @test */
    public function remove_product_with_count_from_cart()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create();
        $cart = new Cart();
        $cart->clear();
        $cart->addProduct($product, 3);
        $cart->addProduct(Product::factory()->create());
        $product->count = 3;

        $this->deleteJson(
            route('cart.remove_product', compact('product')),
            ['count' => 2]
        )
            ->assertSuccessful();

        $cart->refresh();

        $this->assertEquals(1, $cart->getProducts()->find($product->id)->count);
    }
}
