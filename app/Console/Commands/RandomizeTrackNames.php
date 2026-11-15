<?php

namespace App\Console\Commands;

use App\Helpers\Helpers;
use App\Models\File;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RandomizeTrackNames extends Command
{
    protected $signature = 'tracks:randomize-names';

    protected $description = 'Rename track files to random names so they do not reveal the movie';

    public function handle(): int
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

        $this->info("Renamed $renamed file(s).");
        return self::SUCCESS;
    }
}
