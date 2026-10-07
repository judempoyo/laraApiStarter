<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\SocialiteLoginAction;
use App\Enums\AuthStatus;
use App\Enums\ErrorCode;
use App\Enums\Result\Auth\LoginResult;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuthResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    /**
     * Return the Google OAuth redirect URL for the client to navigate to.
     */
    public function redirectToGoogle(): JsonResponse
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return ApiResponse::success(['redirect_url' => $url], 'Google OAuth redirect URL generated.');
    }

    /**
     * Handle the Google callback, authenticate and return a Sanctum token.
     */
    public function handleGoogleCallback(SocialiteLoginAction $action): JsonResponse
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable $e) {
            return ApiResponse::error(
                ErrorCode::INVALID_CREDENTIALS,
                'Unable to retrieve Google information. Please try again.',
                401
            );
        }

        $result = $action->execute($socialUser, 'google');

        if (($result['status'] ?? null) === LoginResult::TWO_FACTOR_REQUIRED) {
            return ApiResponse::success([
                'auth_status'         => AuthStatus::TWO_FACTOR_REQUIRED->value,
                'two_factor_required' => true,
                'token'               => $result['token'],
                'token_type'          => $result['token_type'],
            ], __('api.2fa_required') ?: 'Two-factor authentication required.');
        }

        return ApiResponse::success([
            'auth_status'         => AuthStatus::AUTHENTICATED->value,
            'two_factor_required' => false,
            'user'                => AuthResource::make($result['user']),
            'token'               => $result['token'],
            'token_type'          => $result['token_type'],
            'expires_at'          => $result['expires_at'],
        ], 'Google login successful.');
    }
}
