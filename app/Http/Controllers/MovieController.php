<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use PhpParser\Builder;

class MovieController extends Controller
{
    public function index(): \Illuminate\Database\Query\Builder
    {
        return DB::table('files')->where('type', 'movie');
    }

    public function getMovieNamesAndIds(): Response
    {
        return response(self::index()->select('id', 'name')->get() ,200);
    }

}
