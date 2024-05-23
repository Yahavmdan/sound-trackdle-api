<?php

namespace App\Http\Requests\File;

use App\Http\Requests\BaseRequest;

class UploadRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:mp3',
        ];
    }
}
