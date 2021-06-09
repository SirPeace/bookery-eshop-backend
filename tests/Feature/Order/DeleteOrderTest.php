<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteOrderTest extends TestCase
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
    public function order_can_be_deleted()
    {
        $order = Order::factory()
            ->hasAttached(
                $product = Product::factory()->create(),
                [
                    'product_title' => $product->title,
                    'old_price' => $product->old_price,
                    'price' => $product->price,
                    'discount' => $product->discount,
                    'product_count' => 1,
                ]
            )
            ->create();

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_product', 1);

        $this->actingAs($this->admin)
            ->deleteJson(route('orders.destroy', compact('order')))
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_product', 0);
    }

    /** @test */
    public function order_is_not_deleted_if_user_is_unauthorized()
    {
        $order = Order::factory()->create();
        $manager = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'manager'])
        ]);

        $this->actingAs(User::factory()->create())
            ->deleteJson(route('orders.destroy', compact('order')))
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseHas('orders', ['id' => $order->id]);
    }
}
