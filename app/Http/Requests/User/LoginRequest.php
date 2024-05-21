<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;

class LoginRequest extends BaseRequest
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
        ];
    }
}
