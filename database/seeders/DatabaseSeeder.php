<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Create the test user using 'username'
    \App\Models\User::factory()->create([
        'username' => 'Test User',
        'email' => 'test@example.com',
    ]);

    // NOW call the OfficeSeeder
    $this->call([
        OfficeSeeder::class,
    ]);
}
}
