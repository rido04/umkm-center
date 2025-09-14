<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kelurahan = [
            [
                'name' => 'Kelurahan Pinang',
                'email' => 'kelpinang@gmail.com',
                'password' => 'pinangisthebest223',
            ],
            [
                'name' => 'Kelurahan Ciledug',
                'email' => 'kelciledug@gmail.com',
                'password' => 'ciledugpanas121',
            ],
            [
                'name' => 'Kelurahan Pasar Kemis',
                'email' => 'kelpakem@gmail.com',
                'password' => 'pakemjauh454',
            ],

        ];
        foreach ($kelurahan as $kel){
            User::create($kel);
        }
    }
}
