<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuessController extends Controller
{
    /** Guesses per game; the last one is made at step LAST_STEP. */
    private const GUESSES = 4;
    private const LAST_STEP = self::GUESSES - 1;

    public function guess(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => 'required|string|size:32',
            'movie_id' => 'nullable|integer',
            'step' => 'required|integer|min:0|max:' . self::GUESSES,
        ]);

        /* @var File|null $file */
        $file = File::query()->where('token', $data['token'])->first();
        if (!$file) return response()->json(['message' => 'File not found'], 404);

        $correct = isset($data['movie_id']) && (int)$data['movie_id'] === $file->id;
        // The last guess (or skip) ends the game, so the answer is revealed with it.
        // Per-IP guess counting is not reliable behind Railway's proxy, so it is not used.
        $gameOver = $data['step'] >= self::LAST_STEP;

        $response = ['correct' => $correct];
        if ($correct || $gameOver) {
            $response += [
                'name' => $file->name,
                'year' => $file->year,
                'poster_url' => $file->poster_url,
                'imdb_url' => $file->imdbUrl(),
            ];
        }
        return response()->json($response);
    }
}
