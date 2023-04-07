<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\FaceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [AuthenticatedSessionController::class, 'apiStore'])->name('login');
Route::post('register', [RegisteredUserController::class, 'apiStore'])->name('register');
Route::get('getRules', function (){
    return \App\Models\VotingRule::all();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', function (Request $request) { return $request->user();});
    Route::post('logout', [AuthenticatedSessionController::class, 'apiDestroy'])->name('logout');
    Route::post('face', [FaceController::class, 'store'])->name('face');
    require __DIR__.'/common.php';
});
