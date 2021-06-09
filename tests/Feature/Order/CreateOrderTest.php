<?php

namespace Tests\Feature\Order;

use App\Cart;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderStatus;
use App\Exceptions\EmptyCartException;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateOrderTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'admin'])
        ]);

        $this->data = [
            'user_id' => $this->admin->id,
            'status_id' => OrderStatus::factory()->create()->id,
            'customer_name' => $this->admin->name,
            'customer_email' => $this->admin->email,
            'customer_phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'city' => $this->faker->city,
            'postcode' => $this->faker->postcode,
            'customer_note' => $this->faker->paragraphs(asText: true),
        ];
    }

    /** @test */
    public function can_create_order()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $cart = new Cart();
        $cart->addProduct(Product::factory()->create());

        $this->postJson(route('orders.store'), $this->data)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'order' => $this->data,
                ]
            ]);

        $this->assertDatabaseHas('orders', ['user_id' => $this->admin->id]);
    }

    /** @test */
    public function order_is_not_created_if_validation_fails()
    {
        $invalidData = array_merge($this->data, ['customer_name' => '']);

        $this->actingAs(User::factory()->create())
            ->postJson(route('orders.store'), $invalidData)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    "customer_name" => [
                        "The customer name field is required."
                    ]
                ]
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('orders', ['user_id' => $this->admin->id]);
    }

    /** @test */
    public function order_cannot_be_created_with_empty_cart()
    {
        $this->actingAs(User::factory()->create());

        $cart = new Cart();
        $cart->clear();

        $this->postJson(route('orders.store'), $this->data)
            ->assertJson([
                "message" => "Can't create order with empty cart",
                "exception" => EmptyCartException::class
            ])
            ->assertStatus(500);
    }

    /** @test */
    public function cart_products_are_stored_in_database_if_order_is_created()
    {
        $this->actingAs(User::factory()->create());

        $cart = new Cart();
        [$productOne, $productTwo] = Product::factory()->createMany([
            [
                'title' => 'Product one',
                'old_price' => 500,
                'price' => 450,
                'discount' => 10,
            ],
            [
                'title' => 'Product two',
                'old_price' => 1000,
                'price' => 750,
                'discount' => 25,
            ]
        ]);
        $cart->addProduct($productOne);
        $cart->addProduct($productTwo, 2);

        $response = $this->postJson(route('orders.store'), $this->data)
            ->assertSuccessful()
            ->json();

        $this->assertDatabaseHas('order_product', [
            'product_id' => $productOne->id,
            'product_title' => 'Product one',
            'old_price' => 500,
            'price' => 450,
            'discount' => 10,
        ]);
        $this->assertDatabaseHas('order_product', [
            'product_id' => $productTwo->id,
            'product_title' => 'Product two',
            'old_price' => 1000,
            'price' => 750,
            'discount' => 25,
            'product_count' => 2,
        ]);

        $order = Order::find($response['data']['order']['id']);

        $this->assertTrue(
            $order->products->contains($productOne) &&
            $order->products->contains($productTwo)
        );
    }
}
