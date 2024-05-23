<?php

namespace App\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class MovieController extends Controller
{
    public function index(): Builder
    {
        return DB::table('files')->where('type', 'movie');
    }

    public function getMovieNamesAndIds(): Response
    {
        return response(self::index()->select('id', 'name')->get() ,200);
    }

}
