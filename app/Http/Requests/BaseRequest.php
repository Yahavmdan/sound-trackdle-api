<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * This is a base request class that extends Laravel's FormRequest. It serves as the foundation for other request classes
 * in the application, providing default behavior for authorization and validation.
 */
class BaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
