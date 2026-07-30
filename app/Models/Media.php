<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'thumbnail_path',
        'original_name',
        'mime_type',
        'size',
        'collection',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the public URL for the file.
     * Uses a temporary signed URL for private disks (s3, r2).
     */
    public function url(int $minutes = 60): string
    {
        $disk = Storage::disk($this->disk);

        if (in_array($this->disk, ['s3', 'r2'], true)) {
            return $disk->temporaryUrl($this->path, now()->addMinutes($minutes));
        }

        return $disk->url($this->path);
    }

    /**
     * Get the thumbnail URL if one exists.
     */
    public function thumbnailUrl(int $minutes = 60): ?string
    {
        if (! $this->thumbnail_path) {
            return null;
        }

        $disk = Storage::disk($this->disk);

        if (in_array($this->disk, ['s3', 'r2'], true)) {
            return $disk->temporaryUrl($this->thumbnail_path, now()->addMinutes($minutes));
        }

        return $disk->url($this->thumbnail_path);
    }

    /**
     * Determine whether this file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Human-readable file size.
     */
    public function humanSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->size;
        $i     = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
