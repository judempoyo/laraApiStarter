<?php

declare(strict_types=1);

namespace App\Enums;

enum ExportFormat: string
{
    case CSV  = 'csv';
    case JSON = 'json';
    case XLSX = 'xlsx';
}
