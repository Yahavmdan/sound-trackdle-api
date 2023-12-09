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
Route::post('teacher/login', [TeacherController::class, 'loginTeacher']);
Route::post('student/login', [StudentController::class, 'loginStudent']);

Route::group(['middleware' => ['auth:sanctum', 'ability:teacher']], function () {
    // student
    Route::get('student', [StudentController::class, 'getStudents']);
    Route::get('student/{periodId}', [StudentController::class, 'getStudentsByPeriod']);
    Route::get('student/period/teacher/{teacherId}', [StudentController::class, 'getStudentsByTeacherPeriod']);
    Route::put('student/{student}', [StudentController::class, 'updateStudent']);
    Route::delete('student/{student}', [StudentController::class, 'destroyStudent']);
    Route::post('student/{studentId}/period/{periodId}', [StudentController::class, 'associateWithPeriod']);
    Route::delete('student/{studentId}/period/{periodId}', [StudentController::class, 'removeFromPeriod']);

    // period
    Route::get('period', [PeriodController::class, 'getPeriods']);
    Route::get('period/{teacherId}', [PeriodController::class, 'getPeriodsByTeacher']);
    Route::post('period', [PeriodController::class, 'storePeriod']);
    Route::put('period/{period}', [PeriodController::class, 'updatePeriod']);
    Route::delete('period/{period}', [PeriodController::class, 'destroyPeriod']);

    // teacher
    Route::get('teacher', [TeacherController::class, 'getTeachers']);
    Route::put('teacher/{teacher}', [TeacherController::class, 'updateTeacher']);
    Route::delete('teacher/{teacher}', [TeacherController::class, 'destroyTeacher']);
    Route::post('teacher/{period}/{teacherId}', [TeacherController::class, 'associateWithPeriod']);
});

