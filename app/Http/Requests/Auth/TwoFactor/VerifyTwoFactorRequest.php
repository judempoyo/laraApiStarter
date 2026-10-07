<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class VerifyTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'min:6', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The two-factor authentication code or recovery code is required.',
            'code.string'   => 'The code must be a string.',
            'code.min'      => 'The code must be at least 6 characters.',
            'code.max'      => 'The code may not exceed 30 characters.',
        ];
    }
}
