<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\AbstractPaginator;

class ApiResponse
{
    /**
     * Send a success response.
     */
    public static function success($data = null, ?string $message = null, int $code = 200, array $meta = []): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if (! empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Send an error response.
     */
    public static function error(string $error_message = 'Error message', string $message = 'Message', int $code = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $code,
                'message' => $error_message,
            ],
            'message' => $message,
            'data' => null,
        ], $code);
    }

    /**
     * Send a paginated response.
     */
    public static function paginated(AbstractPaginator $paginator, ?string $message = null, int $code = 200): JsonResponse
    {
        $items = $paginator->items();

        $meta = [
            'api_version' => config('app.api_version'),
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'total_items' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
            'count' => count($items),
            'is_empty' => count($items) === 0,
            'has_more_pages' => $paginator->hasMorePages(),
        ];

        if (app()->environment('local')) {
            $meta['debug'] = [
                'memory_usage' => memory_get_peak_usage(true),
                'query_count' => \DB::getQueryLog() ? count(\DB::getQueryLog()) : 0,
            ];
        }

        return self::success($items, $message, $code, $meta);
    }
}
