<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class Helpers
{
    static function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $string));
    }

    static function getDate(): int
    {
        return Carbon::now()->get('day') .+ Carbon::now()->get('month') .+ Carbon::now()->get('year');
    }
}
