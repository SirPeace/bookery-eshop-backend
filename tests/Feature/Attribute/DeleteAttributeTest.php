<?php

namespace Tests\Feature\Attribute;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Attribute;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->attribute = Attribute::factory()->create();

        $this->manager = User::factory()->create(
            [
                'role_id' => Role::factory()->create(['slug' => 'manager'])->id
            ]
        );
    }

    /** @test */
    public function unauthorized_user_cannot_delete_attribute()
    {
        $guest = User::factory()->create();

        $response = $this->deleteJson(
            route(
                'attributes.destroy',
                ['attribute' => $this->attribute]
            )
        );

        $response->assertStatus(401)->assertJson(
            ['message' => 'Unauthenticated.']
        );

        $response = $this->actingAs($guest)
            ->deleteJson(
                route(
                    'attributes.destroy',
                    ['attribute' => $this->attribute]
                )
            );

        $response->assertStatus(403)->assertJson(
            ['message' => 'This action is unauthorized.']
        );
    }

    /** @test */
    public function can_delete_attribute()
    {
        $this->assertDatabaseHas(
            'attributes',
            $this->attribute->toArray()
        );

        $response = $this->actingAs($this->manager)
            ->deleteJson(
                route(
                    'attributes.destroy',
                    ['attribute' => $this->attribute]
                )
            );

        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing(
            'attributes',
            $this->attribute->toArray()
        );
    }
}
