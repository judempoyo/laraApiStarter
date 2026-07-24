<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\ApiRequest;

class UpdateProfileRequest extends ApiRequest

{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitize text inputs before validation to prevent XSS.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name') && is_string($this->input('name'))) {
            $this->merge([
                'name' => htmlspecialchars(strip_tags($this->input('name')), ENT_QUOTES, 'UTF-8'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please provide your name.',
            'name.max'      => 'Your name cannot exceed 100 characters.',
        ];
    }
}
