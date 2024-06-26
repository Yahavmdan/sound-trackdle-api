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
use ZipArchive;

class FileController extends Controller
{
    public function upload(UploadRequest $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');
        $zip = new ZipArchive;
        $filePath = $file->getRealPath();

        if ($zip->open($filePath) !== TRUE) {
            return response()->json(['message' => 'Failed to open ZIP file'], 400);
        }

        $extractPath = storage_path('app/public/tracks/');
        $zip->extractTo($extractPath);
        $zip->close();

        $extractedFiles = array_diff(scandir($extractPath), array('.', '..'));

        foreach ($extractedFiles as $extractedFile) {
            $filePath = 'tracks/' . $extractedFile;
            $model = File::query()->where('id', Helpers::getFirstPart($extractedFile))->first();
            $model?->update(['file_path' => $filePath]);
        }

        return response()->json(['message' => 'Files uploaded and extracted successfully']);
    }

    public function massDelete(): JsonResponse
    {
        $files = Storage::disk('public')->files('tracks');

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
            File::query()->where('file_path', $file)->update(['file_path' => null]);
        }

        return response()->json(['message' => 'All files deleted successfully']);
    }

    public function index(): JsonResponse
    {
        $files = Storage::disk('public')->files('tracks');
        return response()->json(['files' => $files]);
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
            ->orderBy('played_at', 'desc')
            ->limit(9)
            ->get()
            ->reverse()
            ->values();
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

        if ($file) return response($file, 200);

        $file = File::query()
            ->whereNotNull('played_at')
            ->orderBy('played_at')
            ->select('id', 'main_actor', 'year', 'genre')
            ->first();

        if ($file) {
            $file->update(['played_at' => Carbon::today()]);
            return response($file, 200);
        }

        return response(['message' => 'File not found'], 404);
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
