<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Http\Requests\File\UploadRequest;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function upload(UploadRequest $request): JsonResponse
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->storeAs('tracks', $fileName, 'public');
            $model = File::query()->where('id', Helpers::getFirstPart($fileName))->first();
            $model->update(['file_path' => 'tracks/' . $fileName]);

            return response()->json(['message' => 'File uploaded successfully']);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function stream(Request $request): Response|string
    {
        /* @var File $file */
        $file = File::query()->where('id', $request->get('id'))->first();
        if (Storage::disk('public')->exists($file->file_path)) {
            if (!$file->played_at) {
                $file->update(['played_at' => Carbon::today()]);
            }
            $fileUrl = Storage::disk('public')->url($file->file_path);
            return response(['path' => $fileUrl], 200);
        }

        return response(['message' => 'File not found'], 404);
    }

    public function getRecentFiles(): Response
    {
        /* @var File $files */
        $files = File::query()
            ->where('played_at', '<', Carbon::today())
            ->whereNotNull('file_path')
            ->select('id', 'main_actor', 'year', 'genre', 'played_at')
            ->limit(7)
            ->get();
        if (!$files) return response(['message' => 'Files not found'], 404);

        return response($files, 200);
    }

    public function getFile(): Response
    {
        /* @var File $file */
        $file = File::query()
            ->where(fn($query) => $query
                ->whereNull('played_at')
                ->orWhere('played_at', '>=', Carbon::today()))
            ->whereNotNull('file_path')
            ->select('id', 'main_actor', 'year', 'genre')
            ->first();
        if (!$file) return response(['message' => 'File not found'], 404);

        return response($file, 200);
    }

    public function getFileById(Request $request): Response
    {
        /* @var File $file */
        $file = File::query()
            ->where('id', $request->get('id'))
            ->first();
        if (!$file) return response(['message' => 'File not found'], 404);

        return response($file, 200);
    }

}
