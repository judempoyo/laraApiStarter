<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class ResetTwoFactorWithRecoveryCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'         => ['nullable', 'email'],
            'password'      => ['required', 'string'],
            'recovery_code' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email'            => 'The email must be a valid email address.',
            'password.required'      => 'Your current password is required.',
            'recovery_code.required' => 'The recovery code is required.',
        ];
    }
}
