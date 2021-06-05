<?php

namespace Tests\Unit;

use App\Cart;
use Tests\TestCase;
use App\Models\User;
use App\Exceptions\CartException;
use App\Models\Product;
use Illuminate\Support\Facades\Redis;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->products = Product::factory(3)->create();
    }

    /** @test */
    public function it_cannot_be_instantiated_for_unauthenticated_user()
    {
        $this->expectException(CartException::class);

        app(Cart::class);
    }

    /** @test */
    public function it_can_fetch_products()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Redis::hmset("cart:test:{$user->id}", [1 => 4, 2 => 10]);

        $cart = app(Cart::class);
        $products = $cart->getProducts();

        $this->assertContainsOnlyInstancesOf(Product::class, $products);
    }

    /** @test */
    public function it_provides_count_property_for_fetched_products()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach ($this->products as $product) {
            Redis::hmset("cart:test:{$user->id}", [$product->id => 72]);
        }

        $cart = app(Cart::class);
        $products = $cart->getProducts();

        $this->assertTrue(
            $products->every(fn ($product) => $product->count == 72)
        );
    }

    /** @test */
    public function it_can_add_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $cart = app(Cart::class);

        $product = Product::factory()->create();
        Redis::hdel("cart:test:{$user->id}", $product->id);

        $cart->addProduct($product, 20);

        $this->assertContains((string) $product->id, Redis::hkeys("cart:test:{$user->id}"));
        $this->assertEquals(20, Redis::hget("cart:test:{$user->id}", $product->id));
    }

    /** @test */
    public function it_adds_product_to_precached_collection_too()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $cart = app(Cart::class);

        $product = Product::factory()->create();
        Redis::hdel("cart:test:{$user->id}", $product->id);

        $cart->addProduct($product);
        $products = $cart->getProducts();

        $this->assertTrue($products->contains($product));
    }

    /** @test */
    public function it_can_remove_product()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $cart = app(Cart::class);

        $product = Product::factory()->create();
        Redis::hset("cart:test:{$user->id}", $product->id, 1);

        $cart->removeProduct($product);

        $this->assertNotContains($product->id, Redis::hkeys("cart:test:{$user->id}"));
    }

    /** @test */
    public function it_removes_product_from_precached_collection_too()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $cart = app(Cart::class);

        $product = Product::factory()->create();
        Redis::hset("cart:test:{$user->id}", $product->id, 1);

        $cart->removeProduct($product);
        $products = $cart->getProducts();

        $this->assertFalse($products->contains($product));
    }

    /** @test */
    public function it_is_unique_for_each_user()
    {
        // One user
        $this->actingAs(User::factory()->create());
        $cart = app(Cart::class);

        $cart->addProduct(Product::factory()->create());
        $cartOne = $cart->getProducts();

        // Another user
        $this->actingAs(User::factory()->create());
        $cart = app(Cart::class);

        $cartTwo = $cart->getProducts();

        $this->assertNotEquals($cartOne, $cartTwo);
    }
}
