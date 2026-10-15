<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use App\Actions\Security\LogSecurityEventAction;
use App\Events\ActivityRecorded;
use App\Events\SecurityEventOccurred;
use App\Events\UserAuthenticated;
use App\Events\UserLoggedIn;
use App\Http\Middleware\SecurityHeadersMiddleware;
use App\Models\AuditLog;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class SecurityAuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app[Kernel::class]->pushMiddleware(SecurityHeadersMiddleware::class);
        Event::listen(SecurityEventOccurred::class, function ($event): void {
            app(LogSecurityEventAction::class)->execute($event->user, $event->event, $event->meta);
        });
        Event::listen(ActivityRecorded::class, function ($event): void {
            AuditLog::create([
                'user_id' => $event->userId ?? auth()->id(),
                'action' => $event->action,
                'description' => $event->description,
                'ip_address' => request()->ip() ? hash('sha256', request()->ip().config('app.key')) : null,
                'user_agent' => request()->userAgent(),
            ]);
        });
        Event::listen(UserLoggedIn::class, fn ($event) => app(LogSecurityEventAction::class)->execute($event->user, 'LOGIN_SUCCESS'));
        Event::listen(UserAuthenticated::class, function ($event): void {
            $last = $event->user->securityLogs()->where('event', 'LOGIN_SUCCESS')->latest()->first();
            if (! $last) {
                return;
            }
            $ip = request()->ip() ? hash('sha256', request()->ip().config('app.key')) : null;
            if ($last->ip_address !== $ip) {
                app(LogSecurityEventAction::class)->execute($event->user, 'IP_CHANGED', ['from' => $last->ip_address, 'to' => $ip]);
            }
            $device = request()->header('User-Agent');
            if ($last->device !== $device) {
                app(LogSecurityEventAction::class)->execute($event->user, 'DEVICE_CHANGED', ['from' => $last->device, 'to' => $device]);
            }
        });
    }
}
