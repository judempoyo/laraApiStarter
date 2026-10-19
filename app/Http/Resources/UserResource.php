<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'account_type' => $this->determineAccountType(),
            'email' => $this->email,
            'is_email_verified' => $this->email_verified_at !== null,
            'roles' => method_exists($this->resource, 'roles') ? $this->resource->roles->pluck('name') : [],
            // 'permissions'        => $this->getAllPermissions()->pluck('name'),
            'has_password' => $this->password !== null,
            'email_verified_at' => $this->email_verified_at,
            'status' => $this->status,
            'is_active' => $this->status === UserStatus::ACTIVE,
            'two_factor_enabled' => method_exists($this->resource, 'hasTwoFactorEnabled') && $this->resource->hasTwoFactorEnabled(),
            'avatar_url' => $this->resource->getAttribute('avatar_url'),
            'provider' => $this->resource->getAttribute('provider'),
            // 'unread_notifications_count' => $this->unreadNotifications()->count(),
            'password_updated_at' => $this->password_updated_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

    }
}
