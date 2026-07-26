<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\StartImpersonationAction;
use App\Contracts\Auth\TokenServiceInterface;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    /**
     * Impersonate a target user — returns a token scoped to that user.
     */
    public function start(Request $request, int $userId, StartImpersonationAction $action): JsonResponse
    {
        $target = User::find($userId);

        if (! $target) {
            throw ApiException::notFound('User');
        }

        if ($request->user()->id === $target->id) {
            throw ApiException::unprocessable(
                'You cannot impersonate yourself.',
                \App\Enums\ErrorCode::IMPERSONATION_SELF
            );
        }

        if ($target->hasRole('admin')) {
            throw ApiException::forbidden('Admins cannot be impersonated.');
        }

        $result = $action->execute($request->user(), $target);

        return ApiResponse::success([
            'user'       => UserResource::make($result['user']),
            'token'      => $result['token'],
            'token_type' => $result['token_type'],
            'expires_at' => $result['expires_at'],
        ], "Now impersonating {$target->email}. Use this token for subsequent requests.");
    }

    /**
     * Stop impersonation — revoke the current impersonation token.
     */
    public function stop(Request $request, TokenServiceInterface $tokenService): JsonResponse
    {
        $tokenService->revokeCurrentToken($request->user());

        return ApiResponse::noContent('Impersonation session ended.');
    }
}
