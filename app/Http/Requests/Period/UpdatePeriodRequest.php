<?php

namespace App\Http\Requests\Period;

use App\Http\Requests\BaseRequest;

class UpdatePeriodRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:255',
        ];
    }
}
