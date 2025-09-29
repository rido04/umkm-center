<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperRegion
 */
class Region extends Model
{
    protected $fillable = ['name'];

    public function umkms()
        {
            return $this->hasMany(Umkm::class);
        }

}
