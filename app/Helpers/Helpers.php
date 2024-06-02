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

    /**
     * @throws Exception
     */
    static function handleZipFile($file): void
    {
        $zip = new ZipArchive;
        $filePath = $file->getRealPath();

        if ($zip->open($filePath) !== TRUE) {
            throw new Exception('Failed to open ZIP file');
        }

        $extractPath = storage_path('app/public/tracks/');
        $zip->extractTo($extractPath);
        $zip->close();

        self::processExtractedFiles($extractPath);
    }

    static function handleMp3File($file): void
    {
        $filePath = 'tracks/' . $file->getClientOriginalName();
        $file->storeAs('public/tracks', $file->getClientOriginalName());

        self::updateFileModel($file->getClientOriginalName(), $filePath);
    }

    private static function processExtractedFiles($extractPath): void
    {
        $extractedFiles = array_diff(scandir($extractPath), array('.', '..'));

        foreach ($extractedFiles as $extractedFile) {
            $fileExtension = pathinfo($extractedFile, PATHINFO_EXTENSION);
            $filePath = $extractPath . $extractedFile;

            if (strtolower($fileExtension) != 'mp3') {
                unlink($filePath);
                continue;
            }

            $relativeFilePath = 'tracks/' . $extractedFile;
            self::updateFileModel($extractedFile, $relativeFilePath);
        }
    }

    private static function updateFileModel($fileName, $filePath): void
    {
        $model = File::query()->where('id', Helpers::getFirstPart($fileName))->first();
        $model?->update(['file_path' => $filePath]);
    }
}
