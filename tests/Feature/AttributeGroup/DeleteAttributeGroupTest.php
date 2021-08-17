<?php

namespace Tests\Feature\AttributeGroup;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteAttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->attributeGroup = AttributeGroup::factory()->create();

        $this->manager = User::factory()->create(
            [
                'role_id' => Role::factory()->create(['slug' => 'manager'])->id
            ]
        );
    }

    /** @test */
    public function unauthorized_user_cannot_delete_attribute_group()
    {
        $guest = User::factory()->create();

        $response = $this->deleteJson(
            route(
                'attribute_groups.destroy',
                ['group' => $this->attributeGroup]
            )
        );

        $response->assertStatus(401)->assertJson(
            ['message' => 'Unauthenticated.']
        );

        $response = $this->actingAs($guest)
            ->deleteJson(
                route(
                    'attribute_groups.destroy',
                    ['group' => $this->attributeGroup]
                )
            );

        $response->assertStatus(403)->assertJson(
            ['message' => 'This action is unauthorized.']
        );
    }

    /** @test */
    public function can_delete_attribute_group()
    {
        $this->assertDatabaseHas(
            'attribute_groups',
            $this->attributeGroup->toArray()
        );

        $response = $this->actingAs($this->manager)
            ->deleteJson(
                route(
                    'attribute_groups.destroy',
                    ['group' => $this->attributeGroup]
                )
            );

        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing(
            'attribute_groups',
            $this->attributeGroup->toArray()
        );
    }
}
