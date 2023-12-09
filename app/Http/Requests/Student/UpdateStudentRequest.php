<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;

class UpdateStudentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'username'  => 'required|string',
            'full_name' => 'required|string',
            'grade'     => 'required|int|required|between:0,12'
        ];
    }
}
