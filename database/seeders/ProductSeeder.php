<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'umkm_id' => 1,
                'name' => 'Nasi Goreng Janda Spesial',
                'description' => 'Nasi goreng dengan rasa goyangan janda.',
                'price' => 15000,
                'image' => 'images/products/pantat.jpg'
            ],
            [
                'umkm_id' => 1,
                'name' => 'Es Teh Jablay',
                'description' => 'Minuman segar pelepas nafsu.',
                'price' => 5000,
                'image' => 'images/products/esteh.jpg'
            ],
            [
                'umkm_id' => 2,
                'name' => 'Batik Tulis Premium',
                'description' => 'Batik tulis asli dengan motif klasik.',
                'price' => 250000,
                'image' => 'images/products/batik.jpg'
            ]
        ];

        foreach ($products as $data) {
            Product::create($data);
        }
    }
}
