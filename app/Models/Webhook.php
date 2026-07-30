<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\WebhookStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webhook extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'url',
        'events',
        'secret',
        'is_active',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'events'    => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class);
    }

    /**
     * Check if this webhook is subscribed to the given event.
     */
    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? [], true);
    }
}
