<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $region = [
            ['name' => 'Ciledug'],
            ['name' => 'Pasar Kemis'],
            ['name' => 'Pinang'],
        ];

        foreach ($region as $name){
            Region::create($name);
        }
    }
}
