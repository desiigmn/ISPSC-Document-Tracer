<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// ADD THIS LINE BELOW:
use Illuminate\Support\Facades\DB;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void {
        DB::table('campuses')->insert([
            ['name' => 'MAIN', 'code' => '0001'],
            ['name' => 'CANDON', 'code' => '0010'],
            ['name' => 'SANTIAGO', 'code' => '0011'],
            ['name' => 'STA. MARIA', 'code' => '0100'],
            ['name' => 'TAGUDIN', 'code' => '0101'],
            ['name' => 'NARVACAN', 'code' => '0110'],
            ['name' => 'CERVANTES', 'code' => '0111'],
        ]);
    }
}