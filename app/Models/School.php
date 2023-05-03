<?php

namespace App\Models;

use App\Models\Common as Model;

class School extends Model
{
    protected $fillable = [
        'name',
        'shortcut',
        'college'
    ];
}
