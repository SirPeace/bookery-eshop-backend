<?php

namespace Tests\Feature\Attribute;

use App\Models\Attribute;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateAttributeTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->create(
            [
                'role_id' => Role::factory()->create(['slug' => 'manager'])->id
            ]
        );

        $this->attribute = Attribute::factory()->create();

        $attributeGroup = AttributeGroup::factory()->create(
            ['slug' => 'slug']
        );

        $this->data = [
            'group_id' => $attributeGroup->id,
            'value' => 'Attribute value'
        ];
    }

    /** @test */
    public function unauthorized_user_cannot_create_attribute()
    {
        $guest = User::factory()->create();

        $this->patchJson(
            route(
                'attributes.update',
                ['attribute' => $this->attribute]
            ),
            $this->data
        )
            ->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);

        $this->actingAs($guest)
            ->patchJson(
                route(
                    'attributes.update',
                    ['attribute' => $this->attribute]
                ),
                $this->data
            )
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    /** @test */
    public function cannot_update_attribute_with_invalid_data()
    {
        $response = $this->actingAs($this->manager)
            ->patchJson(
                route(
                    'attributes.update',
                    ['attribute' => $this->attribute]
                ),
                [
                    'value' => '',
                    'group_id' => -1
                ]
            );

        $response->assertStatus(422)
            ->assertJson(
                [
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        "value" => [
                            "The value must be a string."
                        ],
                        "group_id" => [
                            "The selected group id is invalid."
                        ]
                    ]
                ]
            );
    }

    /** @test */
    public function can_update_attribute_with_valid_data()
    {
        $response = $this->actingAs($this->manager)
            ->patchJson(
                route(
                    'attributes.update',
                    ['attribute' => $this->attribute]
                ),
                $this->data
            );

        $response->assertJson(
            [
                'status' => 'success',
                'data' => [
                    'attribute' => [
                        'value' => $this->data['value'],
                        'group_id' => $this->data['group_id']
                    ]
                ]
            ]
        );

        $this->assertDatabaseHas('attributes', $this->data);
    }
}
