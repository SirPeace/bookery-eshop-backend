<?php

namespace Tests\Feature\AttributeGroup;

use Tests\TestCase;
use App\Models\User;
use App\Models\AttributeGroup;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'title' => 'Attribute group',
            'description' => 'Attribute group description',
            'slug' => 'attribute-group'
        ];
    }

    /** @test */
    public function unauthorized_user_cannot_create_attribute_group()
    {
        $guest = User::factory()->create();

        $this->postJson(route('attribute_groups.store'), $this->data)
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        $this->actingAs($guest)
            ->postJson(route('attribute_groups.store'), $this->data)
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    /** @test */
    public function cannot_create_attribute_group_with_invalid_data()
    {
        $manager = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'manager'])->id
        ]);

        AttributeGroup::factory()->create(['slug' => 'slug']);

        $response = $this->actingAs($manager)
            ->postJson(route('attribute_groups.store'), [
                'title' => '',
                'description' => 0,
                'slug' => 'slug'
            ]);

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'title' => [
                    'The title field is required.'
                ],
                "description" => [
                    "The description must be a string."
                ],
                'slug' => [
                    'The slug has already been taken.'
                ],
            ]
        ]);
    }

    /** @test */
    public function can_create_attribute_group_with_valid_data()
    {
        $manager = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'manager'])->id
        ]);

        $response = $this->actingAs($manager)
            ->postJson(route('attribute_groups.store'), $this->data);

        $response->assertJson([
            'status' => 'success',
            'data' => [
                'attribute_group' => [
                    'title' => $this->data['title'],
                    'description' => $this->data['description'],
                    'slug' => $this->data['slug']
                ]
            ]
        ]);

        $this->assertDatabaseHas('attribute_groups', $this->data);
    }
}
