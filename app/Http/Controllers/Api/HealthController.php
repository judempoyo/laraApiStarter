<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class HealthController extends Controller
{
    /**
     * API health check endpoint.
     *
     * Returns status of core services: database, cache, queue, and storage.
     */
    public function __invoke(): JsonResponse
    {
        $services = [
            'database' => $this->checkDatabase(),
            'cache'    => $this->checkCache(),
            'queue'    => $this->checkQueue(),
            'storage'  => $this->checkStorage(),
        ];

        $isHealthy = collect($services)->every(fn (array $s): bool => $s['status'] === 'up');

        $health = [
            'status'      => $isHealthy ? 'healthy' : 'degraded',
            'timestamp'   => now()->toIso8601String(),
            'version'     => config('api.version', 'v1'),
            'environment' => app()->environment(),
            'services'    => $services,
        ];

        $statusCode = $isHealthy ? 200 : 503;
        $message    = $isHealthy ? 'All systems operational.' : 'Some services are degraded.';

        $response = ApiResponse::success($health, $message, $statusCode);

        if (! $isHealthy) {
            $response->headers->set('Retry-After', '30');
        }

        return $response;
    }

    /**
     * Check database connectivity.
     */
    protected function checkDatabase(): array
    {
        try {
            $start   = microtime(true);
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

    /**
     * Check queue driver connectivity.
     */
    protected function checkQueue(): array
    {
        try {
            $connection = config('queue.default', 'sync');

            if ($connection === 'sync') {
                return ['status' => 'up', 'driver' => 'sync'];
            }

            // Attempt to connect to the queue driver
            Queue::size();

            return ['status' => 'up', 'driver' => $connection];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }

    /**
     * Check default storage disk accessibility.
     */
    protected function checkStorage(): array
    {
        try {
            $disk = Storage::disk(config('filesystems.default', 'local'));
            $disk->put('.health', 'ok');
            $exists = $disk->exists('.health');
            $disk->delete('.health');

            return ['status' => $exists ? 'up' : 'down'];
        } catch (\Throwable $e) {
            return ['status' => 'down', 'error' => $e->getMessage()];
        }
    }
}
