<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'umkm_id', 'name', 'description', 'price', 'image'
    ];

    public function umkm()
    {
        return $this->belongsTo(Umkm::class);
    }
}
