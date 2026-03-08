<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ApiRequest;

class LoginRequest extends ApiRequest

{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please provide your email address.',
            'email.email'    => 'The email address must be a valid format.',
            'password.required' => 'Your password is required to log in.',
        ];
    }

}
