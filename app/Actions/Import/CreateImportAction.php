<?php

declare(strict_types=1);

namespace App\Actions\Import;

use App\DTOs\Import\CreateImportDTO;
use App\Enums\ImportStatus;
use App\Jobs\ProcessImportJob;
use App\Models\Import;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Http\UploadedFile;

class CreateImportAction
{
    public function __construct(
        private readonly MediaService $mediaService,
    ) {}

    public function execute(UploadedFile $file, User $user, CreateImportDTO $dto): Import
    {
        // 1. Upload the file temporarily using MediaService
        $disk = config('import.disk', 'local');
        $media = $this->mediaService->upload(
            file: $file,
            user: $user,
            collection: 'imports',
            disk: $disk
        );

        // 2. Create the Import tracking record
        $import = Import::create([
            'user_id'  => $dto->userId,
            'resource' => $dto->resource,
            'status'   => ImportStatus::PENDING,
            'media_id' => $media->id,
            'dry_run'  => $dto->dryRun,
        ]);

        // 3. Dispatch the processing job
        ProcessImportJob::dispatch($import);

        return $import;
    }
}
