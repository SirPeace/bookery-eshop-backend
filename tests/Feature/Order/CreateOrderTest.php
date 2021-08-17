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

        $this->user = User::factory()->create();

        $this->data = [
            'status_id' => OrderStatus::factory()->create()->id,
            'customer_name' => $this->user->name,
            'customer_email' => $this->user->email,
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
        $this->actingAs($this->user);

        $cart = new Cart();
        $cart->clear();

        $cart->addProduct(Product::factory()->create());

        $this->postJson(route('orders.store'), $this->data)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'order' => [
                        'user_id' => $this->user->id,
                        'status_id' => $this->data['status_id'],
                        'customer_name' => $this->data['customer_name'],
                        'customer_email' => $this->data['customer_email'],
                        'customer_phone' => $this->data['customer_phone'],
                        'address' => $this->data['address'],
                        'city' => $this->data['city'],
                        'postcode' => $this->data['postcode'],
                        'customer_note' => $this->data['customer_note'],
                    ],
                ]
            ]);

        $this->assertDatabaseHas('orders', ['user_id' => $this->user->id]);
    }

    /** @test */
    public function order_is_not_created_if_validation_fails()
    {
        $invalidData = [
            'status_id' => -1,
            'customer_name' => '',
            'customer_email' => 'foo',
            'customer_phone' => 'going',
            'address' => '',
            'city' => '',
            'postcode' => '',
            'customer_note' => false,
            'customer_name' => '',
        ];

        $response = $this->actingAs($this->user)
            ->postJson(route('orders.store'), $invalidData);

        $response
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    "status_id" => [
                        "The selected status id is invalid."
                    ],
                    "customer_name" => [
                        "The customer name field is required."
                    ],
                    "customer_email" => [
                        "The customer email must be a valid email address."
                    ],
                    "customer_phone" => [
                        "The customer phone format is invalid."
                    ],
                    "address" => [
                        "The address field is required."
                    ],
                    "city" => [
                        "The city field is required."
                    ],
                    "postcode" => [
                        "The postcode field is required."
                    ],
                    "customer_note" => [
                        "The customer note must be a string."
                    ]
                ]
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('orders', ['user_id' => $this->user->id]);
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
        $this->actingAs($this->user);

        $cart = new Cart();
        $cart->clear();

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
            ->assertSuccessful();

        $this->assertDatabaseHas('order_product', [
            'order_id' => $response->json('data.order.id'),
            'product_id' => $productOne->id,
            'product_title' => 'Product one',
            'product_old_price' => 500,
            'product_price' => 450,
            'product_discount' => 10,
            'product_count' => 1,
        ]);
        $this->assertDatabaseHas('order_product', [
            'order_id' => $response->json('data.order.id'),
            'product_id' => $productTwo->id,
            'product_title' => 'Product two',
            'product_old_price' => 1000,
            'product_price' => 750,
            'product_discount' => 25,
            'product_count' => 2,
        ]);

        $order = Order::find($response['data']['order']['id']);

        $this->assertTrue(
            $order->products->contains($productOne) &&
            $order->products->contains($productTwo)
        );
    }
}
