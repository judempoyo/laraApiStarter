<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $ttl = config('export.url_ttl_minutes', 60);

        return [
            'id'            => $this->id,
            'resource'      => $this->getAttribute('resource'),
            'format'        => $this->format?->value,
            'status'        => $this->status?->value,
            'filters'       => $this->filters,
            'download_url'  => $this->isCompleted() && $this->media
                ? $this->media->url($ttl)
                : null,
            'error_message' => $this->when($this->isFailed(), $this->error_message),
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
