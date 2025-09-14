<?php

namespace Database\Seeders;

use App\Models\Umkm;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UmkmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $umkms = [
            [
                'name' => 'Warung Janda',
                'description' => 'Menjual makanan khas ulekan pantat jablay.',
                'address' => 'Jl. jablay no 69',
                'phone' => '696969696969',
                'region_id' => 1
            ],
            [
                'name' => 'Toko Batik Indah',
                'description' => 'Menyediakan batik tulis dan cap khas daerah.',
                'address' => 'Tes jalan 2',
                'phone' => '0000087773437',
                'region_id' => 2
            ]
        ];

        foreach ($umkms as $data) {
            Umkm::create($data);
        }
    }
}
