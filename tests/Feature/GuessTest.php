<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuessTest extends TestCase
{
    use RefreshDatabase;

    private File $answer;
    private File $other;

    protected function setUp(): void
    {
        parent::setUp();
        $base = ['type' => 'movie', 'main_actor' => 'A', 'genre' => 'G'];
        $this->answer = File::query()->create($base + ['name' => 'Avatar', 'year' => 2009, 'file_path' => 'tracks/0000000000000001.mp3',
            'difficulty' => 'easy', 'poster_url' => 'https://img/p.jpg', 'imdb_id' => 'tt0499549', 'play_order' => 1]);
        $this->other = File::query()->create($base + ['name' => 'Titanic', 'year' => 1997, 'file_path' => 'tracks/0000000000000002.mp3', 'play_order' => 2]);
    }

    public function test_file_hides_id(): void
    {
        $res = $this->getJson('/api/file')->assertOk();
        $this->assertArrayNotHasKey('id', $res->json());
        $this->assertArrayNotHasKey('name', $res->json());
        $this->assertSame($this->answer->token, $res->json('token'));
        $this->assertSame('easy', $res->json('difficulty'));
        $this->assertArrayNotHasKey('poster_url', $res->json());
        $this->assertArrayNotHasKey('imdb_id', $res->json());
    }

    public function test_correct_guess_reveals(): void
    {
        $this->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => $this->answer->id, 'step' => 0])
            ->assertOk()->assertExactJson(['correct' => true, 'name' => 'Avatar', 'year' => 2009,
                'poster_url' => 'https://img/p.jpg', 'imdb_url' => 'https://www.imdb.com/title/tt0499549/']);
    }

    public function test_wrong_guess_hides_name(): void
    {
        $res = $this->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => $this->other->id, 'step' => 0])
            ->assertOk()->assertExactJson(['correct' => false]);
    }

    public function test_reveal_after_four_guesses(): void
    {
        foreach ([0, 1, 2] as $step) {
            $this->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => null, 'step' => $step])
                ->assertExactJson(['correct' => false]);
        }
        // 4th (last) guess is made at step 3
        $this->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => $this->other->id, 'step' => 3])
            ->assertOk()->assertExactJson(['correct' => false, 'name' => 'Avatar', 'year' => 2009,
                'poster_url' => 'https://img/p.jpg', 'imdb_url' => 'https://www.imdb.com/title/tt0499549/']);
    }

    public function test_no_reveal_last_step_without_guesses(): void
    {
        $this->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => null, 'step' => 3])
            ->assertOk()->assertExactJson(['correct' => false]);
    }

    public function test_unknown_token_404(): void
    {
        $this->postJson('/api/guess', ['token' => str_repeat('x', 32), 'movie_id' => 1, 'step' => 0])->assertNotFound();
        $this->postJson('/api/stream', ['token' => str_repeat('x', 32)])->assertNotFound();
    }

    public function test_movie_list_has_year(): void
    {
        $this->getJson('/api/movie')->assertOk()->assertJsonFragment(['name' => 'Avatar', 'year' => 2009]);
    }

    public function test_reveal_counts_guesses_per_real_client_ip_behind_proxy(): void
    {
        // each request arrives from a different proxy hop, same real client
        foreach ([0, 1, 2] as $step) {
            $this->withServerVariables(['REMOTE_ADDR' => "100.64.0.$step"])
                ->withHeaders(['X-Forwarded-For' => '203.0.113.7'])
                ->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => null, 'step' => $step])
                ->assertExactJson(['correct' => false]);
        }
        $this->withServerVariables(['REMOTE_ADDR' => '100.64.0.9'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.7'])
            ->postJson('/api/guess', ['token' => $this->answer->token, 'movie_id' => null, 'step' => 3])
            ->assertOk()->assertJsonPath('name', 'Avatar');
    }
}
