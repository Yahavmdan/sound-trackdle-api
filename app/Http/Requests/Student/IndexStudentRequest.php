<?php

namespace App\Http\Requests\Student;

use App\Http\Requests\BaseRequest;

class IndexStudentRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules(): array
    {
        return [
            'teacher_id'    => 'nullable|int',
            'period_id'     => 'nullable|int',
        ];
    }
}
