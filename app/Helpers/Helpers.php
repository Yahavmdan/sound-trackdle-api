<?php

namespace App\Helpers;

use App\Models\File;
use Exception;
use ZipArchive;

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

    /**
     * This method takes a file name that must start with {number}{_}{name}.
     * example: 34_Avatar.mp3, the number must be the same as item id.
     * @param string $input
     * @return int
     */
    static function getFirstPart(string $input): int {
        $firstPart = explode('_', $input)[0];
        return intval($firstPart);
    }
}
