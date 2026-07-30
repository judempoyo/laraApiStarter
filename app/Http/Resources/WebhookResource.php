<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebhookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'url'         => $this->url,
            'events'      => $this->events,
            'is_active'   => $this->is_active,
            'description' => $this->description,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
