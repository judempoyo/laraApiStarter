<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ApiRequest;

class UpdateEmailRequest extends ApiRequest

{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $this->user()?->id],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please provide your new email address.',
            'email.email'    => 'Please provide a valid email address format.',
            'email.unique'   => 'This email address is already associated with an account.',
        ];
    }
}
