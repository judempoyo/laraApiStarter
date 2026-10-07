<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\TwoFactor;

use Illuminate\Foundation\Http\FormRequest;

class ResetTwoFactorViaLinkRequest extends FormRequest
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
            'password.required' => 'Your password is required to reset two-factor authentication.',
            'password.string'   => 'The password must be a string.',
        ];
    }
}
