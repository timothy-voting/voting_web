<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Common extends Model
{
    use HasFactory;
    public array $update_keys = ['*'];
    public array $validations = [];
    public array $selectColumns = ['*'];
    public array $indexColumns = ['*'];
    public array $showColumns = ['*'];
    public string $showModel = 'id';
}
