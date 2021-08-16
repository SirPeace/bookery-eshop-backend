<?php

namespace Tests\Feature\Attribute;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetAttributesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_all_attributes_for_attribute_group()
    {
        $group = AttributeGroup::factory()->create();
        Attribute::factory(2)->create(['group_id' => $group->id]);
        Attribute::factory()->create();

        $this->getJson(route(
            'attributes.index',
            ['group' => $group->id]
        ))
            ->assertJson([
                "status" => "success",
                "data" => [
                    "attributes" => []
                ]
            ])
            ->assertJsonCount(2, 'data.attributes');
    }

    /** @test */
    public function get_single_attribute()
    {
        $attr = Attribute::factory()->create();

        $this->getJson(route('attributes.show', ['attribute' => $attr->id]))
            ->assertJson([
                "status" => "success",
                "data" => [
                    "attribute" => $attr->toArray()
                ]
            ]);
    }
}
