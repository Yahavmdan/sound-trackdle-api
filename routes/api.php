<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\UserController;
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
Route::post('health', [FileController::class, 'health']);
Route::post('user/login', [UserController::class, 'loginUser']);
Route::get('movie', [MovieController::class, 'getMovieNamesAndIds']);
Route::post('stream', [FileController::class, 'stream']);
Route::get('file', [FileController::class, 'getFile']);
Route::get('files', [FileController::class, 'getRecentFiles']);
Route::post('file/id', [FileController::class, 'getFileById']);

Route::group(['middleware' => ['auth:sanctum', 'ability:user']], function () {
    Route::post('upload', [FileController::class, 'upload']);
    Route::delete('delete/all', [FileController::class, 'massDelete']);
});


