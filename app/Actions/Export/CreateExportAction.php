<?php

declare(strict_types=1);

namespace App\Actions\Export;

use App\DTOs\Export\CreateExportDTO;
use App\Jobs\ProcessExportJob;
use App\Models\Export;

class CreateExportAction
{
    public function execute(CreateExportDTO $dto): Export
    {
        $export = Export::create([
            'user_id'  => $dto->userId,
            'resource' => $dto->resource,
            'format'   => $dto->format,
            'status'   => \App\Enums\ExportStatus::PENDING,
            'filters'  => $dto->filters ?: null,
        ]);

        ProcessExportJob::dispatch($export);

        return $export;
    }
}
