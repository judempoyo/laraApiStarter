<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class DisableTwoFactorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Your current password is required to disable two-factor authentication.',
            'password.string'   => 'The password must be a valid string.',
        ];
    }
}
