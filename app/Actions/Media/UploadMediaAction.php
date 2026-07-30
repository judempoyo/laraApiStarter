<?php

declare(strict_types=1);

namespace App\Actions\Media;

use App\DTOs\Media\UploadMediaDTO;
use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;

class UploadMediaAction
{
    public function __construct(private readonly MediaService $mediaService) {}

    public function execute(UploadedFile $file, User $user, UploadMediaDTO $dto): Media
    {
        return $this->mediaService->upload(
            file: $file,
            user: $user,
            collection: $dto->collection,
            disk: $dto->disk,
        );
    }
}
