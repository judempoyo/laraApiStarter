<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\V1\User;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List all notifications for the authenticated user (paginated).
     * Returns both read and unread — ordered newest first.
     */
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->orderByDesc('created_at')
            ->paginate(config('api.pagination.default_per_page', 15));

        return ApiResponse::paginated($notifications, 'Notifications retrieved successfully.');
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (! $notification) {
            throw ApiException::notFound('Notification');
        }

        $notification->markAsRead();

        return ApiResponse::noContent('Notification marked as read.');
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return ApiResponse::noContent('All notifications marked as read.');
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $deleted = $request->user()
            ->notifications()
            ->where('id', $id)
            ->delete();

        if (! $deleted) {
            throw ApiException::notFound('Notification');
        }

        return ApiResponse::noContent('Notification deleted.');
    }
}
