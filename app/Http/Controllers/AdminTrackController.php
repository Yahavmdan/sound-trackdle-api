<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\File\StoreTrackRequest;
use App\Models\File;
use App\Services\TrackNameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AdminTrackController extends Controller
{
    /** Fields the pipeline may always set, also on hand-seeded rows. */
    private const META_FIELDS = ['difficulty', 'poster_url', 'imdb_id'];

    /**
     * Upsert a track. With `file`: stores the clip (random name) and its provenance.
     * Without `file`: metadata-only update of an existing movie.
     */
    public function store(StoreTrackRequest $request): JsonResponse
    {
        $data = $request->validated();

        $file = null;
        if (!empty($data['tmdb_id'])) {
            $file = File::query()->where('tmdb_id', $data['tmdb_id'])->first();
        }
        $file ??= File::query()->where('name', $data['name'])->where('year', $data['year'])->first();

        $fields = array_filter(array_intersect_key($data, array_flip(self::META_FIELDS)), fn($v) => $v !== null);
        if (!empty($data['tmdb_id'])) $fields['tmdb_id'] = $data['tmdb_id'];

        if (!$request->hasFile('file')) {
            if (!$file) return response()->json(['message' => 'Unknown movie; a file is required to create it'], 422);
            $file->update($fields);
            return response()->json(['id' => $file->id, 'created' => false]);
        }

        $path = Helpers::randomTrackPath();
        Storage::disk('public')->putFileAs(dirname($path), $request->file('file'), basename($path));
        $fields += [
            'file_path' => $path,
            'source_url' => $data['source_url'] ?? null,
            'clip_start' => $data['clip_start'] ?? null,
        ];

        if ($file) {
            $old = $file->file_path;
            $file->update($fields);
            if ($old && $old !== $path) Storage::disk('public')->delete($old);
            return response()->json(['id' => $file->id, 'created' => false]);
        }

        $file = File::query()->create($fields + [
            'name' => $data['name'],
            'year' => $data['year'],
            'main_actor' => $data['main_actor'],
            'genre' => $data['genre'],
            'type' => 'movie',
        ]);
        return response()->json(['id' => $file->id, 'created' => true]);
    }

    /** Same as `php artisan tracks:randomize-names`, for hosts without shell access. */
    public function randomizeNames(): JsonResponse
    {
        return response()->json(['message' => 'Renamed ' . TrackNameService::randomizeAll() . ' file(s).']);
    }

    /** Remove a movie's clip (the movie stays in the guess list). */
    public function destroy(int $id): JsonResponse
    {
        /* @var File|null $file */
        $file = File::query()->find($id);
        if (!$file) return response()->json(['message' => 'File not found'], 404);

        if ($file->file_path) Storage::disk('public')->delete($file->file_path);
        $file->update(['file_path' => null, 'source_url' => null, 'clip_start' => null]);
        return response()->json(['id' => $file->id, 'deleted' => true]);
    }
}
