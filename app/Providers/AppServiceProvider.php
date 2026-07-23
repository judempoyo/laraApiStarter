<?php

namespace App\Providers;


use Dedoc\Scramble\Scramble;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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

        $this->configureScramble();
    }

    /**
     * Configure Scramble documentation access.
     */
    protected function configureScramble(): void
    {
        Gate::define('viewApiDocs', function ($user = null) {
            // Allow in local environment by default
            if (app()->environment('local')) {
                return true;
            }

            // Check for a specific cookie/secret to allow access in other environments
            $accessKey = config('app.scramble_access_key', 'lara-api-starter-secret');
            
            return request()->cookie('scramble_access_key') === $accessKey;
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('auth', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });
    }
}
