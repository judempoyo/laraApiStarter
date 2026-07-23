<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    /**
     * Log an action to the audit_logs table.
     */
    protected function logActivity(string $action, ?string $description = null, ?int $userId = null): void
    {
        $ip           = Request::ip();
        $anonymizedIp = $ip ? hash('sha256', $ip . config('app.key')) : null;

        AuditLog::create([
            'user_id'     => $userId ?? Auth::id(),
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $anonymizedIp,
            'user_agent'  => Request::userAgent(),
        ]);

    }
}
