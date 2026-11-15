<?php

namespace App\Http\Requests\File;

use App\Http\Requests\BaseRequest;

class StoreTrackRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|file|max:2048',
            'tmdb_id' => 'nullable|integer|min:1',
            'name' => 'required|string|max:255',
            'year' => 'required|integer|min:1900|max:2100',
            'main_actor' => 'required|string|max:255',
            'genre' => 'required|string|max:255',
            'source_url' => 'nullable|url|max:255',
            'clip_start' => 'nullable|numeric|min:0',
        ];
    }
}
