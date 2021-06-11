<?php

namespace Database\Seeders;

use App\Models\AttributeGroup;
use Illuminate\Database\Seeder;

class AttributeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AttributeGroup::factory()->createMany([
            [
                'title'       => '',
                'slug'        => '',
                'description' => '',
            ],
        ]);
    }
}
