<?php

use App\Http\Controllers\Common;
use Illuminate\Support\Facades\Route;

foreach (['rule', 'student'] as $name){
    Route::resource($name, Common::class);
}
