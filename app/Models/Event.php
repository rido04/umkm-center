<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'image_path',
        'description',
        'places',
        'event_date',
        'start_date',
        'end_date',
    ];

    protected $dates = [
        'event_date',
        'start_date',
        'end_date',
    ];
}
