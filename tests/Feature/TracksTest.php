<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TracksTest extends TestCase
{
    use RefreshDatabase;

    private function makeFile(array $attrs = []): File
    {
        static $n = 0;
        $n++;
        return File::query()->create(array_merge([
            'name' => "Movie $n", 'type' => 'movie', 'year' => 2000,
            'main_actor' => 'Actor', 'genre' => 'Drama', 'file_path' => null,
        ], $attrs));
    }

    public function test_playable_excludes_empty_path(): void
    {
        $this->makeFile(['file_path' => '']);
        $this->makeFile(['file_path' => null]);
        $ok = $this->makeFile(['file_path' => 'tracks/abc.mp3']);

        $this->assertSame([$ok->id], File::query()->playable()->pluck('id')->all());
    }

    public function test_token_generated_on_create(): void
    {
        $file = $this->makeFile();
        $this->assertSame(32, strlen($file->token));
    }

    public function test_randomize_names_renames_and_updates_path(): void
    {
        Storage::fake('public');
        $file = $this->makeFile(['file_path' => 'tracks/7_Avatar.mp3']);
        Storage::disk('public')->put('tracks/7_Avatar.mp3', 'audio');
        Storage::disk('public')->put('tracks/0123456789abcdef.mp3', 'audio');

        $this->artisan('tracks:randomize-names')->assertSuccessful();

        $file->refresh();
        $this->assertMatchesRegularExpression('#^tracks/[0-9a-f]{16}\.mp3$#', $file->file_path);
        Storage::disk('public')->assertExists($file->file_path);
        Storage::disk('public')->assertMissing('tracks/7_Avatar.mp3');
        Storage::disk('public')->assertExists('tracks/0123456789abcdef.mp3');
    }
}
