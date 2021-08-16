<?php

namespace Tests\Feature\AttributeGroup;

use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAttributeGroupsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_all_attribute_groups()
    {
        AttributeGroup::factory(2)->create();

        $this->getJson(route('attribute_groups.index'))
            ->assertJson([
                "status" => "success",
                "data" => [
                    "attribute_groups" => []
                ]
            ])
            ->assertJsonCount(2, 'data.attribute_groups');
    }

    /** @test */
    public function get_single_attribute_group()
    {
        $group = AttributeGroup::factory()->create();

        $this->assertDatabaseHas('attribute_groups', $group->toArray());

        $this->getJson(
            route(
                'attribute_groups.show',
                ['group' => $group->id]
            )
        )
            ->assertJson([
                "status" => "success",
                "data" => [
                    "attribute_group" => $group->toArray()
                ]
            ]);
    }
}
