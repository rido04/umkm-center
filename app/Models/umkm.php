<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperUmkm
 */
class Umkm extends Model
{
    protected $fillable = [
        'name', 'description', 'address', 'phone', 'region_id', 'created_by', 'user_id'
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
