<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Auth\TokenServiceInterface;
use App\Services\Auth\PassportTokenService;
use App\Services\Auth\SanctumTokenService;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TokenServiceInterface::class, function (): TokenServiceInterface {
            return match (config('api.auth_driver', 'sanctum')) {
                'passport' => new PassportTokenService(),
                default    => new SanctumTokenService(),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('local')) {
            \Illuminate\Support\Facades\DB::enableQueryLog();
        }

        $this->configureRateLimiting();
        $this->configureGates();
        $this->configureApiDocs();
    }

    /**
     * Configure Scramble API documentation access.
     */
    protected function configureGates(): void
    {
        $checkAccess = function (?object $user = null): bool {
            if (app()->environment('local')) {
                return true;
            }

            $accessKey = config('app.docs_access_key', 'lara-api-starter-secret');

            return request()->cookie('docs_access_key') === $accessKey;
        };

        Gate::define('viewApiDocs', $checkAccess);
        Gate::define('viewScalar', $checkAccess);
    }

    /**
     * Configure Scramble OpenAPI security scheme.
     */
    protected function configureApiDocs(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi): void {
                $openApi->components->securitySchemes['bearerAuth'] = SecurityScheme::http('bearer');
            });
    }

    /**
     * Configure all rate limiters for the application.
     *
     * All rate limit values are driven by config/api.php → rate_limits.
     */
    protected function configureRateLimiting(): void
    {
        $limits = config('api.rate_limits');

        RateLimiter::for('api', function (Request $request) use ($limits): Limit {
            return Limit::perMinute($limits['api'])
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) use ($limits): Limit {
            return Limit::perMinute($limits['auth'])
                ->by($request->ip());
        });

        RateLimiter::for('login', function (Request $request) use ($limits): Limit {
            return Limit::perMinute($limits['login'])
                ->by(strtolower((string) $request->input('email')) . '|' . $request->ip());
        });

        RateLimiter::for('register', function (Request $request) use ($limits): Limit {
            return Limit::perHour($limits['register'])
                ->by($request->ip());
        });

        RateLimiter::for('password-reset', function (Request $request) use ($limits): Limit {
            return Limit::perHour($limits['password_reset'])
                ->by(strtolower((string) $request->input('email')) . '|' . $request->ip());
        });
    }
}
