<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;

class StoreStudentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
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
