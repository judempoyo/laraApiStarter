<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\CheckEmailAction;
use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Contracts\Auth\TokenServiceInterface;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Enums\ErrorCode;
use App\Enums\Result\Auth\LoginResult;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\CheckEmailRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use \App\Traits\LogsActivity;

    public function __construct(private readonly TokenServiceInterface $tokenService) {}

    /**
     * Check if email exists in the database.
     */
    public function checkEmail(CheckEmailRequest $request, CheckEmailAction $action): JsonResponse
    {
        $result = $action->execute($request->validated('email'));

        return ApiResponse::success(
            ['exists' => $result['exists']],
            $result['exists'] ? __('api.email_exists') : __('api.email_not_found')
        );
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->execute(
            RegisterDTO::fromRequest($request->validated())
        );

        return ApiResponse::created([
            'user'       => AuthResource::make($result['user']),
            'token'      => $result['token'],
            'token_type' => $result['token_type'],
            'expires_at' => $result['expires_at'],
        ], __('api.register_success'));
    }

    /**
     * Login user and return a token.
     */
    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute(
            LoginDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            LoginResult::USER_DISABLED       => throw ApiException::unprocessable(
                __('api.account_disabled'),
                ErrorCode::ACCOUNT_DISABLED
            ),
            LoginResult::INVALID_CREDENTIALS => throw ApiException::unauthorized(),
            LoginResult::SUCCESS             => ApiResponse::success([
                'user'       => AuthResource::make($result['user']),
                'token'      => $result['token'],
                'token_type' => $result['token_type'],
                'expires_at' => $result['expires_at'],
            ], __('api.login_success')),
        };
    }

    /**
     * Logout user (revoke the current token).
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->tokenService->revokeCurrentToken($user);
        $this->logActivity('auth.logout', 'User logged out.', $user->id);

        return ApiResponse::noContent(__('api.logout_success'));
    }

    /**
     * Logout user from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->tokenService->revokeAllTokens($user);
        $this->logActivity('auth.logout_all', 'User logged out from all devices.', $user->id);

        return ApiResponse::noContent(__('api.logout_all_success'));
    }

    /**
     * Revoke a specific session by token ID.
     */
    public function logoutSession(Request $request, int $tokenId): JsonResponse
    {
        $user    = $request->user();
        $deleted = $this->tokenService->revokeTokenById($user, $tokenId);

        if (! $deleted) {
            throw ApiException::notFound('Session');
        }

        $this->logActivity('auth.logout_session', "Session {$tokenId} revoked.", $user->id);

        return ApiResponse::noContent(__('api.session_revoked'));
    }

    /**
     * Revoke all sessions except the current one.
     */
    public function logoutOthers(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->tokenService->revokeOtherTokens($user);
        $this->logActivity('auth.logout_others', 'All other sessions revoked.', $user->id);

        return ApiResponse::noContent(__('api.other_sessions_revoked'));
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return ApiResponse::success(
            UserResource::make($request->user()->load(['roles', 'permissions']))
        );
    }

    /**
     * Verify email address.
     */
    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = \App\Models\User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw ApiException::forbidden(__('api.invalid_verification_link'));
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, __('api.email_already_verified'));
        }

        $user->markEmailAsVerified();

        return ApiResponse::success(null, __('api.email_verified'));
    }

    /**
     * Resend email verification link.
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            throw ApiException::unprocessable(__('api.email_already_verified_err'), ErrorCode::EMAIL_ALREADY_VERIFIED);
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::accepted(__('api.verification_link_sent'));
    }

    /**
     * Refresh the current token.
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        $this->tokenService->revokeCurrentToken($user);

        $deviceName = app(\App\Actions\Auth\ResolveDeviceNameAction::class)->execute();
        $newToken   = $this->tokenService->createToken($user, $deviceName);

        return ApiResponse::success([
            'user'       => AuthResource::make($user->load(['roles', 'permissions'])),
            'token'      => $newToken,
            'token_type' => 'Bearer',
            'expires_at' => $this->tokenService->getTokenExpiry(),
        ], __('api.token_refreshed'));
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request, SendPasswordResetLinkAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return match ($result['status']) {
            \App\Enums\Result\Auth\PasswordResetResult::LINK_SENT => ApiResponse::accepted(__('passwords.sent')),
            \App\Enums\Result\Auth\PasswordResetResult::THROTTLED  => throw ApiException::tooManyRequests(__('passwords.throttled')),
            default                                                => throw ApiException::unprocessable(__('passwords.user'), ErrorCode::USER_NOT_FOUND),
        };
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return match ($result['status']) {
            \App\Enums\Result\Auth\PasswordResetResult::RESET_SUCCESS => ApiResponse::success(null, __('passwords.reset')),
            \App\Enums\Result\Auth\PasswordResetResult::INVALID_TOKEN  => throw ApiException::unprocessable(__('passwords.token'), ErrorCode::INVALID_VALIDATION_LINK),
            \App\Enums\Result\Auth\PasswordResetResult::THROTTLED      => throw ApiException::tooManyRequests(__('passwords.throttled')),
            default                                                    => throw ApiException::unprocessable(__('passwords.user'), ErrorCode::RESET_PASSWORD_FAILED),
        };
    }
}
