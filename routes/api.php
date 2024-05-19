<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\MovieController;
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
Route::post('health', [FileController::class, 'health']);

Route::post('upload', [FileController::class, 'upload']);
Route::get('movie', [MovieController::class, 'getMovieNamesAndIds']);
Route::post('stream', [FileController::class, 'stream']);
Route::get('file', [FileController::class, 'getFile']);
Route::post('file/id', [FileController::class, 'getFileById']);


