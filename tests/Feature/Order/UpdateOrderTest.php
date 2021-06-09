<?php

namespace Tests\Feature\Order;

use Tests\TestCase;
use App\Models\Role;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateOrderTest extends TestCase
{
    use RefreshDatabase, withFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['name' => 'admin'])
        ]);

        $this->order = Order::factory()->create();
    }

    /** @test */
    public function order_can_be_updated()
    {
        $note = $this->faker->paragraphs(asText: true);
        $postcode = $this->faker->postcode();
        $address = $this->faker->address();

        $updatedFields = [
            'customer_name'  => 'Kobe Bryant',
            'customer_email' => 'black.mamba@mail.com',
            'customer_phone' => '+1 100 134 5112',
            'address'        => $address,
            'city'           => 'Los Angeles',
            'postcode'       => $postcode,
            'customer_note'  => $note,
        ];

        $this->actingAs($this->admin)
            ->patchJson(
                route('orders.update', ['order' => $this->order]),
                $updatedFields
            )
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'order' => $updatedFields
                ]
            ]);

        $this->assertDatabaseHas('orders', $updatedFields);
    }

    /** @test */
    public function order_is_not_updated_if_validation_fails()
    {
        $this->actingAs($this->admin)
            ->patchJson(
                route('orders.update', ['order' => $this->order]),
                ['user_id' => 100]
            )
            ->assertStatus(422);

        $this->assertDatabaseMissing('orders', [
            'customer_name' => 'Robert Martin',
        ]);
    }

    /** @test */
    public function returns_errors_if_validation_fails()
    {
        $this->actingAs($this->admin)
            ->patchJson(
                route('orders.update', ['order' => $this->order]),
                ['user_id' => 'smth']
            )
            ->assertJsonFragment([
                'errors' => [
                    'user_id' => ["The user id must be an integer."]
                ]
            ]);
    }

    /** @test */
    public function order_is_not_updated_if_user_is_unauthorized()
    {
        $this->actingAs(User::factory()->create())
            ->patchJson(
                route('orders.update', ['order' => $this->order]),
                ['customer_name' => 'Robert Martin']
            )
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseMissing(
            'orders',
            ['customer_name' => 'Robert Martin']
        );
    }
}
