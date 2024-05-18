<?php

namespace App\Http\Controllers;
use App\Models\File;
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
            $file->storeAs('public/mp3', $fileName);

            return response()->json(['message' => 'File uploaded successfully']);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }

    public function stream(Request $request): Response | string
    {
        $path = $request->get('path');
        if (file_exists($path)) {
            return file_get_contents($path);
        }
        return response(['message' => 'File not found'], 404);
    }

    public function getFile(): Response
    {
        $file = File::query()
            ->where('is_recently_played', 0)
            ->whereNotNull('file_path')
            ->first();
        if (!$file instanceof File) {
            return response(['message' => 'File not found'], 404);
        }
        return response($file, 200);
    }

}
