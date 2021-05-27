<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        (new RoleSeeder)->run();

        User::factory()->create([
            'name' => 'Roman Khabibulin',
            'email' => 'roman.khabibulin12@gmail.com',
            'password' => Hash::make('admin'),
            'role_id' => Role::where('name', 'admin')->first()->id,
        ]);
    }
}
