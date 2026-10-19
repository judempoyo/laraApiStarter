<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use Illuminate\Support\ServiceProvider;

class ExportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $resources = config('export.resources', []);
            $requirements = ['user_preferences' => 'App\\Models\\UserPreference', 'notifications' => 'App\\Providers\\Modules\\NotificationsServiceProvider', 'api_keys' => 'App\\Models\\ApiKey'];
            foreach ($requirements as $key => $class) {
                if (! class_exists($class)) {
                    unset($resources[$key]);
                }
            }
            config(['export.resources' => $resources]);
        });
    }
}
