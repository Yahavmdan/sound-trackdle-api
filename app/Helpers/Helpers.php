<?php

namespace App\Helpers;

class Helpers
{
    static function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $string));
    }


    static function getFilePath(string $fileName): ?string
    {
        $filePath = storage_path('app/public/tracks/' . $fileName);

        if (file_exists($filePath)) {
            return $filePath;
        }
        return null;
    }

    static function snakeToTitleCase($string): string
    {
        $string = str_replace('_', ' ', $string);

        $string = ucwords($string);

        if (($pos = strpos($string, '.')) !== false) {
            $string = substr($string, 0, $pos);
        }

        return $string;
    }
}
