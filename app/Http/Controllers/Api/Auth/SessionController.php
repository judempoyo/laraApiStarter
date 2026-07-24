<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * List all active sessions (tokens) for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()
            ->tokens()
            ->orderByDesc('last_used_at')
            ->get()
            ->map(fn ($token) => [
                'id'           => $token->id,
                'name'         => $token->name,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at'   => $token->created_at->toIso8601String(),
                'expires_at'   => $token->expires_at?->toIso8601String(),
                'is_current'   => $token->id === $request->user()->currentAccessToken()->id,
            ]);

        return ApiResponse::success($tokens, 'Active sessions retrieved.');
    }
}
