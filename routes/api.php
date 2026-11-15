<?php

use App\Http\Controllers\AdminTrackController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GuessController;
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
Route::get('version', [Controller::class, 'getAppVersion']);

Route::post('user/login', [UserController::class, 'loginUser']);
Route::get('movie', [MovieController::class, 'getMovieNamesAndIds']);
Route::post('stream', [FileController::class, 'stream']);
Route::get('file', [FileController::class, 'getFile']);
Route::get('files', [FileController::class, 'getRecentFiles']);
Route::post('guess', [GuessController::class, 'guess'])->middleware('throttle:30,1');

Route::group(['middleware' => ['auth:sanctum', 'ability:user']], function () {
    Route::post('upload', [FileController::class, 'upload']);
    Route::delete('delete/all', [FileController::class, 'massDelete']);
    Route::get('file/index', [FileController::class, 'index']);
    Route::post('admin/tracks', [AdminTrackController::class, 'store']);
});


