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
                'slug' => 'active',
                'name' => 'Active',
            ],
            [
                'slug' => 'archived',
                'name' => 'Archived',
            ],
            [
                'slug' => 'out-of-stock',
                'name' => 'Out of stock',
            ],
        ]);
    }
}
