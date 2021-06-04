<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected Order $order;

    public function setUp(): void
    {
        parent::setUp();

        $this->order = Order::factory()
            ->for(User::factory())
            ->for(OrderStatus::factory(), 'status')
            ->hasAttached(
                Product::factory()->count(3),
                [
                    'product_title' => 'Random title',
                    'old_price' => 1000,
                    'price' => 800,
                    'discount' => 20,
                ]
            )
            ->create();
    }

    /** @test */
    public function order_belongs_to_user()
    {
        $this->assertInstanceOf(User::class, $this->order->user);
    }

    /** @test */
    public function order_has_order_status()
    {
        $this->assertInstanceOf(OrderStatus::class, $this->order->status);
    }

    /** @test */
    public function order_has_many_products()
    {
        $this->assertContainsOnlyInstancesOf(
            Product::class,
            $this->order->products
        );
    }
}
