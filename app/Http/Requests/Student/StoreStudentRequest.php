<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
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
            'grade'     => 'required|int|between:0,12',
        ];
    }
}
