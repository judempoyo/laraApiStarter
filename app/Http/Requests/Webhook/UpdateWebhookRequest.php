<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhook;

use App\Enums\WebhookEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validEvents = array_column(WebhookEvent::cases(), 'value');

        return [
            'url'         => ['sometimes', 'url', 'max:2048'],
            'events'      => ['sometimes', 'array', 'min:1'],
            'events.*'    => ['string', Rule::in($validEvents)],
            'is_active'   => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.url'         => 'The webhook URL must be a valid URL.',
            'events.array'    => 'Events must be provided as an array.',
            'events.min'      => 'At least one event must be selected.',
            'events.*.in'     => 'One or more selected events are invalid.',
            'is_active.boolean' => 'The active status must be true or false.',
        ];
    }
}
