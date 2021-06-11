<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeGroup;
use Illuminate\Database\Seeder;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Attribute::factory()->createMany([
            [
                'group_id' => AttributeGroup::where('slug', '')->first()->id,
                'value'    => '',
            ],
        ]);
    }
}
