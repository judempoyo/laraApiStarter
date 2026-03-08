<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class CheckEmailRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please provide an email address to check.',
            'email.email'    => 'The email address must be a valid format.',
        ];
    }
}
