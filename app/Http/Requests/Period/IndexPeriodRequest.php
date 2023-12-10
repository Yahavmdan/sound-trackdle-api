<?php

namespace App\Http\Requests\Period;

use App\Http\Requests\BaseRequest;

class IndexPeriodRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'teacher_id'    => 'nullable|int',
        ];
    }
}
