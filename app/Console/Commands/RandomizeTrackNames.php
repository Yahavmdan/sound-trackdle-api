<?php

namespace App\Console\Commands;

use App\Services\TrackNameService;
use Illuminate\Console\Command;

class RandomizeTrackNames extends Command
{
    protected $signature = 'tracks:randomize-names';

    protected $description = 'Rename track files to random names so they do not reveal the movie';

    public function handle(): int
    {
        $this->info('Renamed ' . TrackNameService::randomizeAll() . ' file(s).');
        return self::SUCCESS;
    }
}
