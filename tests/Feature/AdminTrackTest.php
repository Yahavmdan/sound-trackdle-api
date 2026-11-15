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
}
