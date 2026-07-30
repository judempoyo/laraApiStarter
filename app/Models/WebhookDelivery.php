<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookDelivery extends Model
{
    use HasFactory;
    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'status',
        'response_code',
        'response_body',
        'attempt',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'payload'      => 'array',
            'status'       => WebhookStatus::class,
            'delivered_at' => 'datetime',
        ];
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === WebhookStatus::SUCCESS;
    }

    public function isFailed(): bool
    {
        return $this->status === WebhookStatus::FAILED;
    }
}
