<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'original_name' => $this->original_name,
            'mime_type'     => $this->mime_type,
            'size'          => $this->size,
            'human_size'    => $this->humanSize(),
            'collection'    => $this->collection,
            'disk'          => $this->disk,
            'is_image'      => $this->isImage(),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
