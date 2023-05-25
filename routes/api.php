<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CandidateController;
use App\Http\Controllers\FaceController;
use App\Http\Controllers\FingerPrintController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\VotingResultController;
use App\Models\Position;
use App\Models\Student;
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

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthenticatedSessionController::class, 'apiStore'])->name('api.login');
    Route::post('register', [RegisteredUserController::class, 'apiStore'])->name('api.register');
    Route::post('face', [FaceController::class, 'store'])->name('face');
    Route::post('fingerprint', [FingerPrintController::class, 'store'])->name('fingerprint');
    Route::get('is_student', function (Request $request) {
        $students = Student::where('student_no', $request->student_no)->get();
        return Response(['isStudent' => count($students) != 0]);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', function (Request $request) {
            return $request->user();
        });
        Route::post('logout', [AuthenticatedSessionController::class, 'apiDestroy'])->name('api.logout');
        Route::get('positions', function () {
            return Position::all();
        });
        Route::get('student', [StudentController::class, 'show'])->name('student');
        Route::get('candidates', [CandidateController::class, 'show'])->name('candidates');
        Route::post('votes', [VotingResultController::class, 'store'])->name('votes.store');
    });


    Route::get('votes', [VotingResultController::class, 'index'])->name('votes.index');
});

