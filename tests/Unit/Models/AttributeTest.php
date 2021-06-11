<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttributeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function belongs_to_attribute_group()
    {
        $attribute = Attribute::factory()
            ->for(AttributeGroup::factory(), 'group')
            ->create();

        $this->assertInstanceOf(AttributeGroup::class, $attribute->group);
    }
}
