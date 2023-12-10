<?php

use App\Http\Controllers\PeriodController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// public teacher
Route::post('teacher', [TeacherController::class, 'store']);
Route::post('student', [StudentController::class, 'store']);

// public student
Route::post('teacher/login', [TeacherController::class, 'loginTeacher']);
Route::post('student/login', [StudentController::class, 'loginStudent']);

Route::group(['middleware' => ['auth:sanctum', 'ability:teacher']], function () {
    // student
    Route::resource('student', StudentController::class)->except(['store']);
    Route::post('student/{studentId}/period/{period}', [StudentController::class, 'attachPeriod']);
    Route::delete('student/{studentId}/period/{period}', [StudentController::class, 'detachPeriod']);

    // period
    Route::resource('period', PeriodController::class);

    // teacher
    Route::resource('teacher', TeacherController::class)->except(['store']);
    Route::post('teacher/{period}/{teacherId}', [TeacherController::class, 'associatePeriod']);
});

