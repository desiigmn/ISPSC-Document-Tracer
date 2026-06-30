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
\App\Models\User::create([
    'username' => 'Head of Records Office',
    'email' => 'brainnnotfound404@gmail.com',
    'password' => bcrypt('12345678'), // Set your password here
    'role' => 'superadmin',
    'office_id' => 'ISPSC-MC-REC-2026-4URQGK',
    'email_verified_at' => now(),
]);

    // NOW call the OfficeSeeder
    $this->call([
        OfficeSeeder::class,
    ]);
}
}
