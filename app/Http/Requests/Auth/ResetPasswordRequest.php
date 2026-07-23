<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ApiRequest;

class ResetPasswordRequest extends ApiRequest

{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
            // 'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        ];
    }
}
