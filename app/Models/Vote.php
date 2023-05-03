<?php

namespace App\Models;

use App\Models\Common as Model;

class Vote extends Model
{
    protected $fillable = [
        'voter_special_number',
        'vote'
    ];

    public $timestamps = false;
}
