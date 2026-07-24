<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;


class RegisterRequest extends ApiRequest

{
    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge([
                'name' => htmlspecialchars(strip_tags($this->input('name')), ENT_QUOTES, 'UTF-8'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            //'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Please enter your full name.',
            'name.max'          => 'Your name is too long. Please keep it under 100 characters.',
            'email.required'    => 'An email address is required to register.',
            'email.email'       => 'Please provide a valid email address format.',
            'email.unique'      => 'This email address is already associated with an account.',
            'password.required' => 'A password is required for your account.',
            'password.min'      => 'Your password must be at least 6 characters long.',
        ];
    }
}
