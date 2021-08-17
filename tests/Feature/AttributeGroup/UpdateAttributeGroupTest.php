<?php

namespace Tests\Feature\AttributeGroup;

use Tests\TestCase;
use App\Models\User;
use App\Models\AttributeGroup;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateAttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'manager'])->id
        ]);

        $this->attributeGroup = AttributeGroup::factory()->create([
            'slug' => 'slug'
        ]);

        $this->data = [
            'title' => 'New attribute group',
            'description' => 'New attribute group description',
            'slug' => 'new-attribute-group'
        ];
    }

    /** @test */
    public function unauthorized_user_cannot_create_attribute_group()
    {
        $guest = User::factory()->create();

        $this->patchJson(
            route('attribute_groups.update', ['attribute_group' => $this->attributeGroup]),
            $this->data
        )
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        $this->actingAs($guest)
            ->patchJson(
                route('attribute_groups.update', ['attribute_group' => $this->attributeGroup]),
                $this->data
            )
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    /** @test */
    public function cannot_update_attribute_group_with_invalid_data()
    {
        $response = $this->actingAs($this->manager)
            ->patchJson(
                route('attribute_groups.update', ['attribute_group' => $this->attributeGroup]),
                [
                    'title' => '',
                    'description' => 0,
                    'slug' => 'slug'
                ]
            );

        $response->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'title' => [
                    'The title must be a string.'
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
    public function can_update_attribute_group_with_valid_data()
    {
        $response = $this->actingAs($this->manager)
            ->patchJson(
                route('attribute_groups.update', ['attribute_group' => $this->attributeGroup]),
                $this->data
            );

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
