<?php

declare(strict_types=1);

namespace App\Enums;

enum ExportStatus: string
{
    case PENDING    = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED  = 'completed';
    case FAILED     = 'failed';
}
