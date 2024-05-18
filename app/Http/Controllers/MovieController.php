<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function index(): JsonResponse
    {
        $movies = DB::table('files')-> where('type', 'movie')->get();
        return response()->json(['movies' => $movies]);
    }

}
