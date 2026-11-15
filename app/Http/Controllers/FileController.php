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
    /** Fields that are safe to send before the game ends (no id, no name). */
    private const PUBLIC_FIELDS = ['token', 'main_actor', 'year', 'genre', 'difficulty'];

    public function upload(UploadRequest $request): JsonResponse
    {
        if (!$request->hasFile('file')) {
            return response()->json(['message' => 'No file uploaded'], 400);
        }

        $zip = new ZipArchive;
        if ($zip->open($request->file('file')->getRealPath()) !== TRUE) {
            return response()->json(['message' => 'Failed to open ZIP file'], 400);
        }

        $tmp = storage_path('app/upload-' . bin2hex(random_bytes(4)));
        $zip->extractTo($tmp);
        $zip->close();

        foreach (array_diff(scandir($tmp), ['.', '..']) as $extractedFile) {
            $model = File::query()->where('id', Helpers::getFirstPart($extractedFile))->first();
            if ($model) {
                $path = Helpers::randomTrackPath();
                Storage::disk('public')->put($path, file_get_contents("$tmp/$extractedFile"));
                $model->update(['file_path' => $path]);
            }
            unlink("$tmp/$extractedFile");
        }
        rmdir($tmp);

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

    public function stream(Request $request): Response
    {
        /* @var File|null $file */
        $file = File::query()->where('token', (string)$request->get('token'))->playable()->first();
        if (!$file || !Storage::disk('public')->exists($file->file_path)) {
            return response(['message' => 'File not found'], 404);
        }

        return response(['path' => Storage::disk('public')->url($file->file_path)], 200);
    }

    public function getRecentFiles(): Response
    {
        $files = File::query()
            ->playable()
            ->where('played_at', '<', Carbon::today())
            ->select([...self::PUBLIC_FIELDS, 'played_at'])
            ->orderBy('played_at', 'desc')
            ->limit(9)
            ->get()
            ->reverse()
            ->values();

        return response($files, 200);
    }

    /**
     * Today's track: the one already picked today, else the next unplayed one in shuffled
     * play order, else (everything played) the one played longest ago. Marks it as played today.
     */
    public function getFile(): Response
    {
        /* @var File|null $file */
        $file = File::query()->playable()->whereDate('played_at', Carbon::today())->orderBy('play_order')->first()
            ?? File::query()->playable()->whereNull('played_at')->orderBy('play_order')->orderBy('id')->first()
            ?? File::query()->playable()->orderBy('played_at')->orderBy('play_order')->first();

        if (!$file) return response(['message' => 'File not found'], 404);

        if (!$file->played_at || !Carbon::parse($file->played_at)->isToday()) {
            $file->update(['played_at' => Carbon::today()]);
        }
        return response($file->only(self::PUBLIC_FIELDS), 200);
    }

    /** Any playable track, independent of the daily schedule (never marks it played). */
    public function random(Request $request): Response
    {
        /* @var File|null $file */
        $file = File::query()->playable()
            ->when($request->query('exclude'), fn($q, $token) => $q->where('token', '!=', $token))
            ->inRandomOrder()
            ->first();

        if (!$file) return response(['message' => 'File not found'], 404);
        return response($file->only(self::PUBLIC_FIELDS), 200);
    }
}
