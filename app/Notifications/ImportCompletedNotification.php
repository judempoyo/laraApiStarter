<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Import;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ImportCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Import $import,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $failed = $this->import->failed_rows;
        $success = $this->import->successful_rows;
        $total = $this->import->total_rows;

        $msg = $this->import->isFailed()
            ? __('api.import_failed_msg', ['resource' => $this->import->resource])
            : __('api.import_completed_msg', [
                'resource' => $this->import->resource,
                'success'  => $success,
                'failed'   => $failed,
                'total'    => $total,
            ]);

        return [
            'type'            => 'import_completed',
            'import_id'       => $this->import->id,
            'resource'        => $this->import->resource,
            'dry_run'         => $this->import->dry_run,
            'total_rows'      => $total,
            'successful_rows' => $success,
            'failed_rows'     => $failed,
            'status'          => $this->import->status->value,
            'message'         => $msg,
        ];
    }
}
