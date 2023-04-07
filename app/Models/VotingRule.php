<?php

namespace App\Models;

use App\Models\Common as Model;

class VotingRule extends Model
{
    protected $fillable = [
        'statement',
    ];

    public array $validations = [
      'statement' => 'required|string'
    ];
}
