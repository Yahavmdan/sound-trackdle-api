<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminTrackTest extends TestCase
{
    use RefreshDatabase;

    private function actAsAdmin(): void
    {
        $user = User::query()->create(['username' => 'admin', 'password' => bcrypt('password123')]);
        Sanctum::actingAs($user, ['user']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'file' => UploadedFile::fake()->create('clip.mp3', 240, 'audio/mpeg'),
            'tmdb_id' => 603,
            'name' => 'The Matrix',
            'year' => 1999,
            'main_actor' => 'Keanu Reeves',
            'genre' => 'Science Fiction, Action',
            'source_url' => 'https://www.youtube.com/watch?v=abc',
            'clip_start' => 42.5,
            'difficulty' => 'easy',
            'poster_url' => 'https://upload.wikimedia.org/poster.jpg',
            'imdb_id' => 'tt0133093',
        ], $overrides);
    }

    public function test_creates_new_track(): void
    {
        Storage::fake('public');
        $this->actAsAdmin();

        $res = $this->post('/api/admin/tracks', $this->payload())->assertOk()->assertJson(['created' => true]);

        $file = File::query()->findOrFail($res->json('id'));
        $this->assertSame('The Matrix', $file->name);
        $this->assertSame(603, $file->tmdb_id);
        $this->assertMatchesRegularExpression('#^tracks/[0-9a-f]{16}\.mp3$#', $file->file_path);
        Storage::disk('public')->assertExists($file->file_path);
    }

    public function test_existing_row_keeps_hand_written_fields(): void
    {
        Storage::fake('public');
        $this->actAsAdmin();
        $seeded = File::query()->create([
            'name' => 'The Matrix', 'type' => 'movie', 'year' => 1999,
            'main_actor' => 'Hand Written', 'genre' => 'Hand Genre',
        ]);

        $this->post('/api/admin/tracks', $this->payload())->assertOk()
            ->assertJson(['id' => $seeded->id, 'created' => false]);

        $seeded->refresh();
        $this->assertSame('Hand Written', $seeded->main_actor);
        $this->assertSame('Hand Genre', $seeded->genre);
        $this->assertSame(603, $seeded->tmdb_id);
        $this->assertNotNull($seeded->file_path);
    }

    public function test_requires_auth(): void
    {
        $this->postJson('/api/admin/tracks', $this->payload())->assertUnauthorized();
    }

    public function test_stores_difficulty_poster_imdb(): void
    {
        Storage::fake('public');
        $this->actAsAdmin();
        $id = $this->post('/api/admin/tracks', $this->payload())->assertOk()->json('id');

        $file = File::query()->findOrFail($id);
        $this->assertSame('easy', $file->difficulty);
        $this->assertSame('tt0133093', $file->imdb_id);
        $this->assertSame('https://upload.wikimedia.org/poster.jpg', $file->poster_url);
    }

    public function test_metadata_only_update_for_existing_row(): void
    {
        $this->actAsAdmin();
        $seeded = File::query()->create([
            'name' => 'The Matrix', 'type' => 'movie', 'year' => 1999,
            'main_actor' => 'Hand Written', 'genre' => 'Hand Genre', 'file_path' => 'tracks/0000000000000001.mp3',
        ]);
        $payload = $this->payload(['difficulty' => 'medium']);
        unset($payload['file'], $payload['source_url'], $payload['clip_start']);

        $this->post('/api/admin/tracks', $payload)->assertOk()->assertJson(['id' => $seeded->id, 'created' => false]);

        $seeded->refresh();
        $this->assertSame('medium', $seeded->difficulty);
        $this->assertSame('tracks/0000000000000001.mp3', $seeded->file_path);
    }

    public function test_metadata_only_for_unknown_movie_is_rejected(): void
    {
        $this->actAsAdmin();
        $payload = $this->payload();
        unset($payload['file']);
        $this->postJson('/api/admin/tracks', $payload)->assertStatus(422);
    }

    public function test_invalid_difficulty_rejected(): void
    {
        $this->actAsAdmin();
        $this->postJson('/api/admin/tracks', $this->payload(['difficulty' => 'insane']))->assertStatus(422);
    }

    public function test_delete_removes_clip(): void
    {
        Storage::fake('public');
        $this->actAsAdmin();
        $id = $this->post('/api/admin/tracks', $this->payload())->json('id');
        $path = File::query()->find($id)->file_path;

        $this->deleteJson("/api/admin/tracks/$id")->assertOk();

        $this->assertNull(File::query()->find($id)->file_path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_randomize_names_endpoint(): void
    {
        Storage::fake('public');
        $this->actAsAdmin();
        $file = File::query()->create(['name' => 'X', 'type' => 'movie', 'year' => 2000, 'main_actor' => 'A',
            'genre' => 'G', 'file_path' => 'tracks/7.mp3']);
        Storage::disk('public')->put('tracks/7.mp3', 'audio');

        $message = $this->postJson('/api/admin/tracks/randomize-names')->assertOk()->json('message');
        $this->assertStringContainsString('Renamed 1 file(s).', $message);
        $this->assertMatchesRegularExpression('#^tracks/[0-9a-f]{16}\.mp3$#', $file->refresh()->file_path);
    }

    public function test_randomize_names_requires_auth(): void
    {
        $this->postJson('/api/admin/tracks/randomize-names')->assertUnauthorized();
    }
}
