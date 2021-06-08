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
                'id'   => 1,
                'slug' => 'customer',
                'name' => 'Customer',
            ],
            [
                'id'   => 2,
                'slug' => 'admin',
                'name' => 'Administrator',
            ],
            [
                'id'   => 3,
                'slug' => 'manager',
                'name' => 'Manager',
            ],
        ]);
    }
}
