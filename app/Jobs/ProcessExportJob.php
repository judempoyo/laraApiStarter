<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ExportableInterface;
use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use App\Enums\WebhookEvent;
use App\Models\Export;
use App\Models\User;
use App\Notifications\ExportReadyNotification;
use App\Services\MediaService;
use App\Services\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Throwable;

class ProcessExportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public function __construct(
        private readonly Export $export,
    ) {}

    public function handle(MediaService $mediaService): void
    {
        $this->export->update(['status' => ExportStatus::PROCESSING]);

        try {
            $exporter = $this->resolveExporter();
            $filters  = $this->export->filters ?? [];

            // Resolve scope: admin exports can target specific users via filters,
            // or all users when null is passed.
            $user = $this->resolveTargetUser($exporter, $filters);

            $content   = $this->generateContent($exporter, $user, $filters);
            $extension = $this->export->format->value === 'xlsx' ? 'csv' : $this->export->format->value;
            $filename  = Str::slug($this->export->resource) . '_' . now()->format('Ymd_His') . '.' . $extension;
            $mimeType  = $this->mimeType();

            // Write to a temp file and wrap as UploadedFile for MediaService.
            $tempPath = sys_get_temp_dir() . '/' . $filename;
            file_put_contents($tempPath, $content);

            $uploadedFile = new UploadedFile(
                path: $tempPath,
                originalName: $filename,
                mimeType: $mimeType,
                test: true,
            );

            $disk    = config('export.disk', 'local');
            $owner   = $this->export->user; // Notifications always go to the requester
            $media   = $mediaService->upload($uploadedFile, $owner, 'exports', $disk);

            @unlink($tempPath);

            $this->export->update([
                'status'   => ExportStatus::COMPLETED,
                'media_id' => $media->id,
            ]);

            $owner->notify(new ExportReadyNotification($this->export, $media));

            WebhookDispatcher::dispatch(WebhookEvent::EXPORT_COMPLETED, [
                'export_id'    => $this->export->id,
                'resource'     => $this->export->resource,
                'format'       => $this->export->format->value,
                'filters'      => $filters,
                'download_url' => $media->url(config('export.url_ttl_minutes', 60)),
            ], $owner->id);

        } catch (Throwable $e) {
            $this->export->update([
                'status'        => ExportStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            WebhookDispatcher::dispatch(WebhookEvent::EXPORT_FAILED, [
                'export_id' => $this->export->id,
                'resource'  => $this->export->resource,
                'error'     => $e->getMessage(),
            ], $this->export->user_id);

            throw $e;
        }
    }

    /**
     * Resolve the ExportableInterface implementation from config.
     */
    private function resolveExporter(): ExportableInterface
    {
        $resources = config('export.resources', []);
        $class     = $resources[$this->export->resource] ?? null;

        if (! $class || ! class_exists($class)) {
            throw new \RuntimeException("No exporter registered for resource: [{$this->export->resource}]");
        }

        return app($class);
    }

    /**
     * Resolve the user scope:
     * - Admin exports with no user_id filter → null (all users)
     * - Admin exports with user_id filter    → specific User model
     * - User-scoped exports                  → the requesting user
     */
    private function resolveTargetUser(ExportableInterface $exporter, array $filters): ?User
    {
        if ($exporter->isAdminOnly()) {
            $userId = $filters['user_id'] ?? null;

            if ($userId) {
                return User::findOrFail((int) $userId);
            }

            return null; // Export all users
        }

        return $this->export->user;
    }

    /**
     * Generate the file content string based on the export format.
     */
    private function generateContent(ExportableInterface $exporter, ?User $user, array $filters): string
    {
        $headers = $exporter->headers();
        $rows    = $exporter->rows($user, $filters);

        return match ($this->export->format) {
            ExportFormat::JSON => json_encode(['headers' => $headers, 'rows' => $rows], JSON_PRETTY_PRINT),
            ExportFormat::CSV, ExportFormat::XLSX => $this->buildCsv($headers, $rows),
        };
    }

    /**
     * Build a CSV string from headers and rows.
     */
    private function buildCsv(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $headers);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Get the MIME type for the export format.
     */
    private function mimeType(): string
    {
        return match ($this->export->format) {
            ExportFormat::JSON => 'application/json',
            ExportFormat::CSV  => 'text/csv',
            ExportFormat::XLSX => 'text/csv', // Falls back to CSV without maatwebsite/excel
        };
    }
}
