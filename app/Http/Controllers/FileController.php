<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\File;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class FileController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $file->storeAs('tracks', $fileName, 'public');
            $file->storeAs('public/mp3', $fileName);
            $model = File::query()->where('name', Helpers::snakeToTitleCase($fileName))->first();
            $model->update(['file_path' => $fileName]);

            return response()->json(['message' => 'File uploaded successfully']);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function stream(Request $request): Response|string
    {
        /* @var File $file */
        $file = File::query()->where('id', $request->get('id'))->first();
        if (env('APP_ENV') === 'local') {
            if (file_exists( 'app/public/mp3/' . $file->file_path)) {
                $file->update(['played_at' => Carbon::today()]);
                return file_get_contents( 'app/public/mp3/' . $file->file_path);
            }
            return response(['message' => 'File not found'], 404);
        }
        if (env('APP_ENV') === 'public') {
            if ($file->file_path) {
                $file->update(['played_at' => Carbon::today()]);
                return response($file->file_path, 200);
            }
            return response(['message' => 'File not found'], 404);
        }

        return response(['message' => 'File not found'], 404);

    }

    public function getFile(): Response
    {
        /* @var File $file */
        $file = File::query()
            ->where(fn($query) => $query
                ->whereNull('played_at')
                ->orWhere('played_at', '>=', Carbon::today()))
            ->whereNotNull('file_path')
            ->select('id', 'main_actor', 'year', 'plot')
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
