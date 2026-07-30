<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ImportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resource',
        'status',
        'media_id',
        'dry_run',
        'total_rows',
        'processed_rows',
        'successful_rows',
        'failed_rows',
        'errors',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'status'  => ImportStatus::class,
            'dry_run' => 'boolean',
            'errors'  => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === ImportStatus::COMPLETED;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [ImportStatus::PENDING, ImportStatus::PROCESSING], true);
    }

    public function isFailed(): bool
    {
        return $this->status === ImportStatus::FAILED;
    }
}
