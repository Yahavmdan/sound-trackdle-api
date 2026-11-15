<?php

namespace App\Services;

use App\Helpers\Helpers;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

class TrackNameService
{
    /**
     * Rename every track file that is not already random-named so file names
     * reveal neither the movie id nor its name. Returns the number renamed.
     */
    public static function randomizeAll(): int
    {
        $disk = Storage::disk('public');
        $renamed = 0;

        foreach ($disk->files('tracks') as $path) {
            if (preg_match('#^tracks/[0-9a-f]{16}\.mp3$#', $path)) {
                continue;
            }
            $newPath = Helpers::randomTrackPath();
            $disk->move($path, $newPath);
            File::query()->where('file_path', $path)->update(['file_path' => $newPath]);
            $renamed++;
        }

        return $renamed;
    }
}
