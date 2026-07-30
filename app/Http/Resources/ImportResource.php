<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'resource'        => $this->getAttribute('resource'),
            'status'          => $this->status?->value,
            'dry_run'         => $this->dry_run,
            'total_rows'      => $this->total_rows,
            'processed_rows'  => $this->processed_rows,
            'successful_rows' => $this->successful_rows,
            'failed_rows'     => $this->failed_rows,
            'errors'          => $this->errors,
            'error_message'   => $this->error_message,
            'media'           => new MediaResource($this->whenLoaded('media')),
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),
        ];
    }
}
