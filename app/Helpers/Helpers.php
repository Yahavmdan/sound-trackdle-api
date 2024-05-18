<?php

namespace App\Helpers;

class Helpers
{
    static function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $string));
    }
}
