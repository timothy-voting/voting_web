<?php

namespace App\Models;

use App\Models\Common as Model;

class FingerPrint extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'path',
        'real_name',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
