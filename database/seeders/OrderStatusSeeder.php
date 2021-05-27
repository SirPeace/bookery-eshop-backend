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
                'name' => 'active',
                'alias' => 'Active',
            ],
            [
                'name' => 'archived',
                'alias' => 'Archived',
            ],
            [
                'name' => 'out-of-stock',
                'alias' => 'Out of stock',
            ],
        ]);
    }
}
