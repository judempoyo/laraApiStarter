<?php

declare(strict_types=1);

namespace App\Exports;

use App\Contracts\ExportableInterface;
use App\Models\User;

class NotificationExport implements ExportableInterface
{
    public function headers(): array
    {
        return ['ID', 'Type', 'Data', 'Read At', 'Created At'];
    }

    public function rows(?User $user, array $filters = []): array
    {
        $query = $user
            ? $user->notifications()
            : \Illuminate\Notifications\DatabaseNotification::query();

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        if (isset($filters['unread_only']) && $filters['unread_only']) {
            $query->whereNull('read_at');
        }

        return $query->orderByDesc('created_at')->get()
            ->map(fn ($notif) => [
                $notif->id,
                class_basename($notif->type),
                json_encode($notif->data),
                $notif->read_at?->toIso8601String(),
                $notif->created_at?->toIso8601String(),
            ])
            ->all();
    }

    public function label(): string
    {
        return 'Notifications';
    }

    public function isAdminOnly(): bool
    {
        return false;
    }
}
