<?php

declare(strict_types=1);

namespace App\Providers\Modules;

use Illuminate\Support\ServiceProvider;

class ImportServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            $resources = config('import.resources', []);
            $requirements = ['user_preferences' => 'App\\Models\\UserPreference', 'notifications' => 'App\\Providers\\Modules\\NotificationsServiceProvider', 'api_keys' => 'App\\Models\\ApiKey'];
            foreach ($requirements as $key => $class) {
                if (! class_exists($class)) {
                    unset($resources[$key]);
                }
            }
            config(['import.resources' => $resources]);
        });
    }
}
