<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttributeGroupTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function has_many_attributes()
    {
        $group = AttributeGroup::factory()
            ->has(Attribute::factory()->count(2))
            ->create();

        $this->assertContainsOnlyInstancesOf(
            Attribute::class, $group->attributes
        );
    }
}
