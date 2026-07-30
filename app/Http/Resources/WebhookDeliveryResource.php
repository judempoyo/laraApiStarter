<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookDeliveryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'webhook_id'    => $this->webhook_id,
            'event'         => $this->event,
            'status'        => $this->status,
            'response_code' => $this->response_code,
            'attempt'       => $this->attempt,
            'delivered_at'  => $this->delivered_at?->toIso8601String(),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
