<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'abilities'       => ['nullable', 'array'],
            'abilities.*'     => ['string'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'A name is required for the API key.',
            'name.string'             => 'The API key name must be a string.',
            'name.max'                => 'The API key name cannot exceed 100 characters.',
            'abilities.array'         => 'Abilities must be provided as an array.',
            'abilities.*.string'      => 'Each ability must be a valid string.',
            'expires_in_days.integer' => 'Expiration days must be an integer.',
            'expires_in_days.min'     => 'Expiration must be at least 1 day.',
            'expires_in_days.max'     => 'Expiration cannot exceed 365 days.',
        ];
    }
}
