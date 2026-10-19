<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\Models\Media;
use App\Services\MediaService;

class DeleteMediaAction
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function execute(Media $media): void
    {
        $this->mediaService->delete($media);
    }
}
