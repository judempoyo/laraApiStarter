<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use App\Enums\Result\Auth\LoginResult;
use App\Events\LoginChallenged;
use App\Http\Middleware\EnsureTwoFactorComplete;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class TwoFactorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $router = $this->app['router'];
        $this->app->booted(fn () => $router->aliasMiddleware('2fa.complete', EnsureTwoFactorComplete::class));
        $this->app[Kernel::class]->appendMiddlewareToGroup('api', EnsureTwoFactorComplete::class);
        $this->app[Kernel::class]->addToMiddlewarePriorityAfter(AuthenticatesRequests::class, EnsureTwoFactorComplete::class);
        Event::listen(LoginChallenged::class, function ($event): void {
            if (! $event->user->hasTwoFactorEnabled()) {
                return;
            }
            $token = $event->user->createToken($event->device, ['two-factor:verify'], now()->addMinutes(5));
            $event->result = [
                'status' => LoginResult::TWO_FACTOR_REQUIRED,
                'user' => $event->user,
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'two_factor_id' => $token->accessToken->id,
            ];
        });
    }
}
