<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ShuffleRandomTest extends TestCase
{
    use RefreshDatabase;

    private function make(int $n, array $attrs = []): File
    {
        return File::query()->create(array_merge([
            'name' => "Movie $n", 'type' => 'movie', 'year' => 2000, 'main_actor' => 'A', 'genre' => 'G',
            'file_path' => sprintf('tracks/%016x.mp3', $n),
        ], $attrs));
    }

    public function test_new_rows_get_random_play_order(): void
    {
        $this->assertNotNull($this->make(1)->play_order);
    }

    public function test_daily_follows_play_order_and_marks_played_on_fetch(): void
    {
        $this->make(1, ['play_order' => 50]);
        $first = $this->make(2, ['play_order' => 10]);

        $token = $this->getJson('/api/file')->assertOk()->json('token');

        $this->assertSame($first->token, $token);
        $this->assertSame(Carbon::today()->toDateString(), substr((string)$first->refresh()->played_at, 0, 10));
        // same track for the rest of the day
        $this->assertSame($token, $this->getJson('/api/file')->json('token'));
    }

    public function test_stream_does_not_mark_played(): void
    {
        $file = $this->make(1);
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put($file->file_path, 'x');

        $this->postJson('/api/stream', ['token' => $file->token])->assertOk();

        $this->assertNull($file->refresh()->played_at);
    }

    public function test_random_returns_playable_track_without_marking_played(): void
    {
        $this->make(1, ['file_path' => null]);
        $playable = $this->make(2);

        $res = $this->getJson('/api/random')->assertOk();

        $this->assertSame($playable->token, $res->json('token'));
        $this->assertArrayNotHasKey('name', $res->json());
        $this->assertNull($playable->refresh()->played_at);
    }

    public function test_random_excludes_given_token(): void
    {
        $a = $this->make(1);
        $b = $this->make(2);
        foreach (range(1, 10) as $_) {
            $this->assertSame($b->token, $this->getJson('/api/random?exclude=' . $a->token)->json('token'));
        }
    }

    public function test_random_skips_recently_heard_then_starts_over(): void
    {
        $a = $this->make(1);
        $b = $this->make(2);
        $c = $this->make(3);
        $this->assertSame($c->token, $this->getJson("/api/random?exclude={$a->token},{$b->token}")->json('token'));
        // all heard: anything but the very last one
        foreach (range(1, 10) as $_) {
            $got = $this->getJson("/api/random?exclude={$a->token},{$b->token},{$c->token}")->assertOk()->json('token');
            $this->assertNotSame($c->token, $got);
        }
    }

    public function test_random_filters_by_difficulty(): void
    {
        $this->make(1, ['difficulty' => 'hard']);
        $easy = $this->make(2, ['difficulty' => 'easy']);
        foreach (range(1, 10) as $_) {
            $this->assertSame($easy->token, $this->getJson('/api/random?difficulty=easy')->json('token'));
        }
        $this->getJson('/api/random?difficulty=medium')->assertNotFound();
        $this->getJson('/api/random?difficulty=bogus')->assertOk();
    }

    public function test_shuffle_resets_history_and_reorders(): void
    {
        Sanctum::actingAs(User::query()->create(['username' => 'admin', 'password' => bcrypt('password123')]), ['user']);
        $played = $this->make(1, ['played_at' => Carbon::yesterday(), 'play_order' => 1]);
        foreach (range(2, 30) as $n) $this->make($n, ['play_order' => $n]);

        $this->postJson('/api/admin/tracks/shuffle')->assertOk()->assertJson(['shuffled' => 30]);

        $this->assertSame(0, File::query()->whereNotNull('played_at')->count());
        $orders = File::query()->orderBy('id')->pluck('play_order')->all();
        $this->assertNotSame(range(1, 30), $orders);
        $this->assertNull($played->refresh()->played_at);
    }

    public function test_shuffle_requires_auth(): void
    {
        $this->postJson('/api/admin/tracks/shuffle')->assertUnauthorized();
    }
}
