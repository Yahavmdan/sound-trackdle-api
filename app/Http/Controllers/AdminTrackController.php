<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\File\StoreTrackRequest;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AdminTrackController extends Controller
{
    public function store(StoreTrackRequest $request): JsonResponse
    {
        $data = $request->validated();

        $file = null;
        if (!empty($data['tmdb_id'])) {
            $file = File::query()->where('tmdb_id', $data['tmdb_id'])->first();
        }
        $file ??= File::query()->where('name', $data['name'])->where('year', $data['year'])->first();

        $path = Helpers::randomTrackPath();
        Storage::disk('public')->putFileAs(dirname($path), $request->file('file'), basename($path));

        $clipFields = [
            'tmdb_id' => $data['tmdb_id'] ?? $file?->tmdb_id,
            'file_path' => $path,
            'source_url' => $data['source_url'] ?? null,
            'clip_start' => $data['clip_start'] ?? null,
        ];

        if ($file) {
            $old = $file->file_path;
            $file->update($clipFields);
            if ($old && $old !== $path) Storage::disk('public')->delete($old);
            return response()->json(['id' => $file->id, 'created' => false]);
        }

        $file = File::query()->create($clipFields + [
            'name' => $data['name'],
            'year' => $data['year'],
            'main_actor' => $data['main_actor'],
            'genre' => $data['genre'],
            'type' => 'movie',
        ]);
        return response()->json(['id' => $file->id, 'created' => true]);
    }
}
