<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username'  => 'required|string',
            'password'  => 'required|string|min:8',
            'full_name' => 'required|string',
            'email'     => 'required|string|email'
        ];
    }
}
