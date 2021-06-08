<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        OrderStatus::factory()->createMany([
            [
                'id' => 1,
                'slug' => 'active',
                'name' => 'Active',
            ],
            [
                'id' => 2,
                'slug' => 'archived',
                'name' => 'Archived',
            ],
            [
                'id' => 3,
                'slug' => 'out-of-stock',
                'name' => 'Out of stock',
            ],
        ]);
    }
}
