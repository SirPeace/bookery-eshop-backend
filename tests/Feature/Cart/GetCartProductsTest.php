<?php

namespace Tests\Feature\Cart;

use App\Cart;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetCartProductsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'admin'])
        ]);
    }

    /** @test */
    public function get_cart_products()
    {
        $this->actingAs($this->admin);

        Product::factory()->create();
        [$productOne, $productTwo] = Product::factory(2)->create();

        $cart = new Cart();
        $cart->clear();
        $cart->addProduct($productOne);
        $cart->addProduct($productTwo, 2);

        $productOne->count = 1;
        $productTwo->count = 2;

        $this->getJson(route('cart.index'))
            ->assertJson([
                "status" => "success",
                "data" => [
                    "cart" => [
                        $productOne->toArray(),
                        $productTwo->toArray()
                    ]
                ]
            ]);
    }
}
