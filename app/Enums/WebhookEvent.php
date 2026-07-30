<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookEvent: string
{
    // API Key events
    case API_KEY_CREATED = 'api_key.created';
    case API_KEY_DELETED = 'api_key.deleted';

    // Export events
    case EXPORT_COMPLETED = 'export.completed';
    case EXPORT_FAILED    = 'export.failed';

    // Import events
    case IMPORT_COMPLETED = 'import.completed';
    case IMPORT_FAILED    = 'import.failed';

    /**
     * Human-readable description of the event.
     */
    public function label(): string
    {
        return match ($this) {
            self::API_KEY_CREATED  => 'API Key Created',
            self::API_KEY_DELETED  => 'API Key Deleted',
            self::EXPORT_COMPLETED => 'Export Completed',
            self::EXPORT_FAILED    => 'Export Failed',
            self::IMPORT_COMPLETED => 'Import Completed',
            self::IMPORT_FAILED    => 'Import Failed',
        };
    }
}
