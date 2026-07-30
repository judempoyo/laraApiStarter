<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Media;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    /**
     * Upload a file to the configured disk and persist a Media record.
     *
     * If the file is an image, a thumbnail is automatically generated
     * and stored alongside the original (requires intervention/image-laravel).
     *
     * @param  UploadedFile  $file        The file to upload.
     * @param  User          $user        The owning user.
     * @param  string        $collection  Logical grouping (e.g. 'documents', 'exports').
     * @param  string|null   $disk        Override disk. Defaults to config('filesystems.default').
     */
    public function upload(UploadedFile $file, User $user, string $collection = 'documents', ?string $disk = null): Media
    {
        $disk     = $disk ?? config('filesystems.default', 'local');
        $folder   = "media/{$user->id}/{$collection}";
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs($folder, $filename, ['disk' => $disk]);

        $thumbnailPath = null;

        // Generate a thumbnail for images if intervention/image is installed.
        if (str_starts_with($file->getMimeType(), 'image/') && class_exists(\Intervention\Image\Laravel\Facades\Image::class)) {
            $thumbnailPath = $this->generateThumbnail($file, $user, $collection, $disk);
        }

        return Media::create([
            'user_id'        => $user->id,
            'disk'           => $disk,
            'path'           => $path,
            'thumbnail_path' => $thumbnailPath,
            'original_name'  => $file->getClientOriginalName(),
            'mime_type'      => $file->getMimeType(),
            'size'           => $file->getSize(),
            'collection'     => $collection,
        ]);
    }

    /**
     * Delete a Media record and its files from storage.
     */
    public function delete(Media $media): void
    {
        $disk = Storage::disk($media->disk);

        $disk->delete($media->path);

        if ($media->thumbnail_path) {
            $disk->delete($media->thumbnail_path);
        }

        $media->delete();
    }

    /**
     * Generate a thumbnail for an uploaded image.
     * Resized to 300×300, maintaining aspect ratio.
     */
    private function generateThumbnail(UploadedFile $file, User $user, string $collection, string $disk): ?string
    {
        try {
            $image     = \Intervention\Image\Laravel\Facades\Image::read($file->getPathname());
            $image->scaleDown(300, 300);

            $folder          = "media/{$user->id}/{$collection}/thumbnails";
            $thumbnailName   = Str::uuid() . '_thumb.' . $file->getClientOriginalExtension();
            $thumbnailPath   = "{$folder}/{$thumbnailName}";

            Storage::disk($disk)->put($thumbnailPath, $image->encodeByMediaType());

            return $thumbnailPath;
        } catch (\Throwable) {
            // Thumbnail generation is non-critical — return null silently.
            return null;
        }
    }

    /**
     * Generate a temporary signed URL for private disks (S3, R2).
     * Falls back to a standard URL for local disk.
     */
    public function temporaryUrl(Media $media, int $minutes = 60): string
    {
        return $media->url($minutes);
    }
}
