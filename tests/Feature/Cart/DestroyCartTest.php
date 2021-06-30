<?php

namespace Tests\Feature;

use App\Cart;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DestroyCartTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function destroy_cart()
    {
        $this->actingAs(User::factory()->create());

        $cart = new Cart();
        $cart->clear();
        $cart->addProduct(Product::factory()->create());
        $cart->addProduct(Product::factory()->create());

        $this->deleteJson(route('cart.destroy'))
            ->assertExactJson([
                "status" => "success"
            ]);

        $cart->refresh();

        $this->assertTrue($cart->getProducts()->isEmpty());
    }
}
