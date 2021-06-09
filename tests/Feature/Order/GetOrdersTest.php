<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetOrdersTest extends TestCase
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
    public function authorized_user_can_get_all_orders()
    {
        [$orderOne, $orderTwo] = Order::factory(2)
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

        $this->actingAs($this->admin)
            ->getJson(route('orders.index'))
            ->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'orders' => [
                        [
                            'user_id' => $orderOne->user_id,
                            'customer_note' => $orderOne->customer_note,
                        ],
                        [
                            'user_id' => $orderTwo->user_id,
                            'customer_note' => $orderTwo->customer_note,
                        ]
                    ],
                ]
            ]);
    }

    /** @test */
    public function unauthorized_user_cannot_get_all_orders()
    {
        $this->getJson(route('orders.index'))
            ->assertStatus(403)
            ->assertJson([
                'message' => 'This action is unauthorized.',
            ]);
    }

    /** @test */
    public function customer_can_get_only_his_orders()
    {
        $user = User::factory()->create();
        Order::factory()
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
            ->createMany([
                ['user_id' => $user->id],
                ['user_id' => User::factory()->create()]
            ]);

        $this->assertEquals(2, Order::count());

        $this->actingAs($user)
            ->getJson(route('orders.index'))
            ->assertJsonCount(1, "data.orders");
    }

    /** @test */
    public function authorized_user_can_get_specific_order()
    {
        $user = User::factory()->create();

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
            ->create([
                'user_id' => $user->id
            ]);

        $this->actingAs($user)
            ->getJson(route('orders.show', compact('order')))
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'order' => [
                        'user_id' => $user->id,
                        'address' => $order->address,
                        'customer_name' => $order->customer_name,
                    ]
                ]
            ]);
    }
}
