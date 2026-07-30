<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ExportFormat;
use App\Enums\ExportStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'resource',
        'format',
        'status',
        'media_id',
        'filters',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'format'  => ExportFormat::class,
            'status'  => ExportStatus::class,
            'filters' => 'array',
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
        return $this->status === ExportStatus::COMPLETED;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [ExportStatus::PENDING, ExportStatus::PROCESSING], true);
    }

    public function isFailed(): bool
    {
        return $this->status === ExportStatus::FAILED;
    }
}
