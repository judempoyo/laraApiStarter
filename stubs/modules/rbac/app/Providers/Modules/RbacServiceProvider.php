<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use App\Events\UserAuthenticated;
use App\Events\UserRegistered;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

class RbacServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $router = $this->app['router'];
        $this->app->booted(function () use ($router): void {
            $router->aliasMiddleware('role', RoleMiddleware::class);
            $router->aliasMiddleware('permission', PermissionMiddleware::class);
            $router->aliasMiddleware('role_or_permission', RoleOrPermissionMiddleware::class);
        });
        Gate::define('api.admin', fn ($user) => $user->hasRole('admin'));
        Event::listen('App\\Events\\UserExportPreparing', function ($event): void {
            $event->query->with('roles');
            if (isset($event->filters['role'])) {
                $event->query->role($event->filters['role']);
            }
        });
        config(['api-starter.seeders' => array_merge(config('api-starter.seeders', []), [RolesAndPermissionsSeeder::class])]);
        Event::listen(UserRegistered::class, function ($event): void {
            if (! $event->user->hasRole('user')) {
                $event->user->assignRole('user');
            }
            $event->user->load(['roles', 'permissions']);
        });
        Event::listen(UserAuthenticated::class, fn ($event) => $event->user->load(['roles', 'permissions']));
    }
}
