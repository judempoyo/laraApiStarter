<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use App\Auth\ApiKeyGuard;
use App\Http\Middleware\AuthenticateWithApiKey;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ApiKeysServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(fn () => $this->app['router']->aliasMiddleware('api.key', AuthenticateWithApiKey::class));
        config(['auth.guards.api-key' => ['driver' => 'api-key', 'provider' => 'users']]);
        Auth::extend('api-key', fn ($app) => new ApiKeyGuard($app['request']));
    }
}
