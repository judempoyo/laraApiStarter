<?php
namespace App\Actions\Security;

use App\Models\User;
use App\Models\UserSecurityLog;

class LogSecurityEventAction
{
    public function execute(
        ?User $user,
        string $event,
        array | string | null $meta = null
    ): void {
        UserSecurityLog::create([
            'user_id'    => $user?->id,
            'event'      => $event,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'device'     => request()?->header('User-Agent'),
            'meta'       => $meta,
        ]);
    }
}
