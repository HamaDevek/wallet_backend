<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Mohammed Kamaran',
            'email' => 'mohammed@email.com',
            'password' => bcrypt('password'), // Ensure the password is hashed
        ]);
        User::factory()->create([
            'name' => 'Ali Al-Sayed',
            'email' => 'ali@email.com',
            'password' => bcrypt('password'), // Ensure the password is hashed
        ]);
    }
}
