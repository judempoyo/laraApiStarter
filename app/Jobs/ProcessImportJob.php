<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\ImportableInterface;
use App\Enums\ImportStatus;
use App\Enums\WebhookEvent;
use App\Models\Import;
use App\Models\User;
use App\Notifications\ImportCompletedNotification;
use App\Services\WebhookDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ProcessImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        private readonly Import $import,
    ) {}

    public function handle(): void
    {
        $this->import->update(['status' => ImportStatus::PROCESSING]);

        $tempPath = null;

        try {
            $importer = $this->resolveImporter();
            $media = $this->import->media;

            if (! $media) {
                throw new \RuntimeException('Media file not found for import.');
            }

            // Retrieve the content from disk and write to temp file
            $content = Storage::disk($media->disk)->get($media->path);
            $tempPath = sys_get_temp_dir() . '/import_' . $this->import->id . '_' . time();
            file_put_contents($tempPath, $content);

            // Parse file into rows
            $parsed = $this->parseFile($tempPath, $importer);
            $rows = $parsed['rows'];
            $totalRows = count($rows);

            $this->import->update([
                'total_rows' => $totalRows,
            ]);

            $processed = 0;
            $success = 0;
            $failed = 0;
            $errors = [];

            $user = $this->import->user;

            foreach ($rows as $index => $row) {
                $rowIndex = $index + 1; // 1-indexed row number
                $rules = $importer->rules($row, $rowIndex);

                // 1. Perform row validation
                $validator = Validator::make($row, $rules);

                if ($validator->fails()) {
                    $errors[] = [
                        'row'    => $rowIndex,
                        'errors' => $validator->errors()->toArray(),
                    ];
                    $failed++;
                } else {
                    // 2. Perform DB Insertion if NOT a dry-run
                    if (! $this->import->dry_run) {
                        try {
                            DB::transaction(function () use ($importer, $row, $user): void {
                                $importer->import($row, $user);
                            });
                            $success++;
                        } catch (Throwable $dbEx) {
                            $errors[] = [
                                'row'    => $rowIndex,
                                'errors' => ['database' => [$dbEx->getMessage()]],
                            ];
                            $failed++;
                        }
                    } else {
                        $success++;
                    }
                }

                $processed++;

                // Periodically update progress (every 50 rows)
                if ($processed % 50 === 0 || $processed === $totalRows) {
                    $this->import->update([
                        'processed_rows'  => $processed,
                        'successful_rows' => $success,
                        'failed_rows'     => $failed,
                        'errors'          => empty($errors) ? null : $errors,
                    ]);
                }
            }

            $this->import->update([
                'status'          => ImportStatus::COMPLETED,
                'processed_rows'  => $processed,
                'successful_rows' => $success,
                'failed_rows'     => $failed,
                'errors'          => empty($errors) ? null : $errors,
            ]);

            // Notify user
            $user->notify(new ImportCompletedNotification($this->import));

            // Dispatch webhook
            WebhookDispatcher::dispatch(WebhookEvent::IMPORT_COMPLETED, [
                'import_id'       => $this->import->id,
                'resource'        => $this->import->resource,
                'dry_run'         => $this->import->dry_run,
                'total_rows'      => $totalRows,
                'successful_rows' => $success,
                'failed_rows'     => $failed,
            ], $user->id);

        } catch (Throwable $e) {
            $this->import->update([
                'status'        => ImportStatus::FAILED,
                'error_message' => $e->getMessage(),
            ]);

            // Notify user
            $this->import->user->notify(new ImportCompletedNotification($this->import));

            // Dispatch Webhook
            WebhookDispatcher::dispatch(WebhookEvent::IMPORT_FAILED, [
                'import_id' => $this->import->id,
                'resource'  => $this->import->resource,
                'error'     => $e->getMessage(),
            ], $this->import->user_id);

            throw $e;
        } finally {
            if ($tempPath && file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Resolve the ImportableInterface implementation from config.
     */
    private function resolveImporter(): ImportableInterface
    {
        $resources = config('import.resources', []);
        $class     = $resources[$this->import->resource] ?? null;

        if (! $class || ! class_exists($class)) {
            throw new \RuntimeException("No importer registered for resource: [{$this->import->resource}]");
        }

        return app($class);
    }

    /**
     * Parse the file (CSV or JSON) into associative arrays matching required headers.
     *
     * @return array{headers: list<string>, rows: list<array<string, mixed>>}
     */
    private function parseFile(string $filePath, ImportableInterface $importer): array
    {
        $mime = $this->import->media->mime_type;
        $required = $importer->requiredHeaders();

        if (str_contains($mime, 'json') || str_ends_with($filePath, '.json')) {
            return $this->parseJson($filePath, $required);
        }

        return $this->parseCsv($filePath, $required);
    }

    /**
     * Parse CSV file.
     */
    private function parseCsv(string $filePath, array $required): array
    {
        $handle = fopen($filePath, 'r');
        if (! $handle) {
            throw new \RuntimeException('Failed to open CSV file for reading.');
        }

        $headers = fgetcsv($handle);
        if (! $headers) {
            fclose($handle);
            throw new \RuntimeException('CSV file is empty or missing headers.');
        }

        $headers = array_map('trim', $headers);

        // Check for missing headers
        $missing = array_diff($required, $headers);
        if (! empty($missing)) {
            fclose($handle);
            throw new \RuntimeException('Missing required CSV headers: ' . implode(', ', $missing));
        }

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (empty($data) || (count($data) === 1 && $data[0] === null)) {
                continue;
            }

            // Fill missing columns with null or trim extra columns
            if (count($data) < count($headers)) {
                $data = array_pad($data, count($headers), null);
            } elseif (count($data) > count($headers)) {
                $data = array_slice($data, 0, count($headers));
            }

            $mappedRow = array_combine($headers, array_map(function ($val) {
                return $val === '' ? null : trim((string) $val);
            }, $data));

            $rows[] = $mappedRow;
        }

        fclose($handle);

        return [
            'headers' => $headers,
            'rows'    => $rows,
        ];
    }

    /**
     * Parse JSON file.
     */
    private function parseJson(string $filePath, array $required): array
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON structure: ' . json_last_error_msg());
        }

        // Support standard array of objects format: [ { "key": "value" }, ... ]
        if (! is_array($data)) {
            throw new \RuntimeException('JSON file must contain an array of objects.');
        }

        // If the JSON is wrapped in a { "rows": [...] } structure, extract it
        if (isset($data['rows']) && is_array($data['rows'])) {
            $data = $data['rows'];
        }

        if (empty($data)) {
            return [
                'headers' => $required,
                'rows'    => [],
            ];
        }

        // Extract headers from the first object
        $headers = array_keys(reset($data));

        $missing = array_diff($required, $headers);
        if (! empty($missing)) {
            throw new \RuntimeException('Missing required JSON keys: ' . implode(', ', $missing));
        }

        return [
            'headers' => $headers,
            'rows'    => $data,
        ];
    }
}
