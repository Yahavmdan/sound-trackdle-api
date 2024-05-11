<?php

namespace App\Http\Controllers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FileController extends Controller
{

    public function health(): JsonResponse
    {
        return response()->json(['message' => 'Ok']);
    }

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

    public function stream(Request $request): bool|JsonResponse|string
    {
        $fileName = $request->input('fileName');
        $filePath = storage_path('app/public/mp3/' . $fileName . '.mp3');

        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }
        return response()->json(['message' => 'File not found'], 404);
    }

}
