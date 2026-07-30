<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhook;

use App\Enums\WebhookEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validEvents = array_column(WebhookEvent::cases(), 'value');

        return [
            'url'         => ['required', 'url', 'max:2048'],
            'events'      => ['required', 'array', 'min:1'],
            'events.*'    => ['string', Rule::in($validEvents)],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required'      => 'A URL is required for the webhook.',
            'url.url'           => 'The webhook URL must be a valid URL.',
            'url.max'           => 'The webhook URL cannot exceed 2048 characters.',
            'events.required'   => 'At least one event must be selected.',
            'events.array'      => 'Events must be provided as an array.',
            'events.min'        => 'At least one event must be selected.',
            'events.*.in'       => 'One or more selected events are invalid.',
            'description.max'   => 'The description cannot exceed 255 characters.',
        ];
    }
}
