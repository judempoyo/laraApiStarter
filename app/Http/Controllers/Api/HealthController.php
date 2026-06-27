<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * API health check endpoint.
     *
     * Returns status of core services: database, cache, and application version.
     */
    public function __invoke(): JsonResponse
    {
        $health = [
            'status'      => 'healthy',
            'timestamp'   => now()->toIso8601String(),
            'version'     => config('app.api_version', 'v1'),
            'environment' => app()->environment(),
            'services'    => [
                'database' => $this->checkDatabase(),
                'cache'    => $this->checkCache(),
            ],
        ];

        $isHealthy = collect($health['services'])->every(fn (array $s) => $s['status'] === 'up');

        if (!$isHealthy) {
            $health['status'] = 'degraded';
        }

        return ApiResponse::success(
            $health,
            $isHealthy ? 'All systems operational.' : 'Some services are degraded.',
            $isHealthy ? 200 : 503
        );
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return ['status' => 'up', 'latency_ms' => $latency];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Check cache connectivity.
     */
    protected function checkCache(): array
    {
        try {
            $key = 'health_check_' . time();
            Cache::put($key, true, 5);
            $result = Cache::get($key);
            Cache::forget($key);

            return ['status' => $result === true ? 'up' : 'down'];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }
}
