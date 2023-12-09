<?php

namespace App\Http\Requests\Teacher;

use App\Http\Requests\BaseRequest;

class StoreTeacherRequest extends BaseRequest
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
            'email'     => 'required|string|email'
        ];
    }
}
