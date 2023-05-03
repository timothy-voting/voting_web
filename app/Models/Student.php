<?php

namespace App\Models;

use App\Models\Common as Model;

class Student extends Model
{
    protected $fillable =[
        'student_no',
        'name',
        'school',
        'college',
        'hall',
        'done'
    ];

    public $timestamps = false;
}
