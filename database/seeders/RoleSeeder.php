<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::factory()->createMany([
            [
                'name' => 'admin',
                'alias' => 'Administrator',
            ],
            [
                'name' => 'manager',
                'alias' => 'Manager',
            ],
            [
                'name' => 'customer',
                'alias' => 'Customer',
            ],
        ]);
    }
}
