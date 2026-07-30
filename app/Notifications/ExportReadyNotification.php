<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Export;
use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExportReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Export $export,
        private readonly Media $media,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ttl = config('export.url_ttl_minutes', 60);

        return [
            'type'         => 'export_ready',
            'export_id'    => $this->export->id,
            'resource'     => $this->export->resource,
            'format'       => $this->export->format->value,
            'download_url' => $this->media->url($ttl),
            'expires_in'   => $ttl . ' minutes',
            'message'      => __('api.export_ready'),
        ];
    }
}
