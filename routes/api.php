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

// public
Route::post('teacher', [TeacherController::class, 'storeTeacher']);
Route::post('student', [StudentController::class, 'storeStudent']);


Route::group(['middleware' => ['auth:sanctum', 'ability:teachers']], function () {
    // student
    Route::get('student', [StudentController::class, 'getStudents']);
    Route::put('student/{student}', [StudentController::class, 'updateStudent']);
    Route::delete('student/{student}', [StudentController::class, 'destroyStudent']);

    // period
    Route::get('period', [PeriodController::class, 'getPeriods']);
    Route::get('period/{teacherId}', [PeriodController::class, 'getPeriodsByTeacher']);
    Route::post('period/{teacher}', [PeriodController::class, 'storePeriod']);
    Route::put('period/{period}', [PeriodController::class, 'updatePeriod']);
    Route::delete('period/{period}', [PeriodController::class, 'destroyPeriod']);

    //teacher
    Route::get('teacher', [TeacherController::class, 'getTeachers']);
    Route::put('teacher/{teacher}', [TeacherController::class, 'updateTeacher']);
    Route::delete('teacher/{teacher}', [TeacherController::class, 'destroyTeacher']);
});

