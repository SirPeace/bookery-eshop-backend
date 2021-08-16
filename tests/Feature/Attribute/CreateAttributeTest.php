<?php

namespace Tests\Feature\Attribute;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $group = AttributeGroup::factory()->create();

        $this->manager = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'manager'])
        ]);

        $this->data = [
            'value' => 'Attrubute value',
            'group_id' => $group->id
        ];
    }

    /** @test */
    public function unauthorized_user_cannot_create_attribute()
    {
        $guest = User::factory()->create();

        $this->postJson(route('attributes.store'), $this->data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        $this->actingAs($guest)
            ->postJson(route('attributes.store'), $this->data)
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    /** @test */
    public function cannot_create_attribute_with_invalid_data()
    {
        $manager = User::factory()->create();

        $response = $this->actingAs($this->manager)
            ->postJson(
                route('attributes.store'),
                [
                    'value' => '',
                    'group_id' => '100'
                ]
            );

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'value' => [
                        'The value field is required.'
                    ],
                    'group_id' => [
                        'The selected group id is invalid.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_create_attribute_with_valid_data()
    {
        $response = $this->actingAs($this->manager)
            ->postJson(route('attributes.store'), $this->data);

        $response->assertJson([
            'data' => [
                'attribute' => [
                    'value' => $this->data['value'],
                    'group_id' => $this->data['group_id']
                ]
            ]
        ]);

        $this->assertDatabaseHas('attributes', $this->data);
    }
}

