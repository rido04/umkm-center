<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Umkm extends Model
{
    protected $fillable = [
        'name', 'description', 'address', 'phone', 'region_id', 'created_by'
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
