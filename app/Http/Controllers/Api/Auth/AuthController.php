<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\SendPasswordResetLinkAction;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use \App\Traits\LogsActivity;

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
        $result = $action->execute(
            RegisterDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            \App\Enums\Auth\RegisterStatus::SUCCESS => ApiResponse::success([
                'user' => UserResource::make($result['data']['user']),
                'token' => $result['data']['token'],
                'token_type' => $result['data']['token_type'],
                'expires_at' => $result['data']['expires_at'],
            ], 'User registered successfully. Please verify your email.', 201),
        };
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute(LoginDTO::fromRequest($request->validated()));

        return match ($result['status']) {
            \App\Enums\Auth\LoginStatus::INVALID_CREDENTIALS => ApiResponse::error(ErrorCode::INVALID_CREDENTIALS, 'Invalid credentials.', 401),
            \App\Enums\Auth\LoginStatus::SUCCESS => ApiResponse::success([
                'user' => UserResource::make($result['data']['user']),
                'token' => $result['data']['token'],
                'token_type' => $result['data']['token_type'],
                'expires_at' => $result['data']['expires_at'],
            ], 'Login successful.'),
        };
    }

    /**
     * Logout user (revoke the token).
     */
public function logout(Request $request): JsonResponse
{
    $user = $request->user();
    $user->currentAccessToken()->delete();

    $this->logActivity('auth.logout', "User logged out.", $user->id);

    return ApiResponse::success(null, 'Logged out successfully.');
}

    /**
     * Logout user from all devices (revoke all tokens).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        $this->logActivity('auth.logout_all', "User logged out from all devices.", $user->id);

        return ApiResponse::success(null, 'Logged out from all devices successfully.');
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return ApiResponse::success(UserResource::make($request->user()->load(['roles', 'permissions'])));
    }

    /**
     * verify email.
     */
    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = \App\Models\User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return ApiResponse::error(ErrorCode::INVALID_VALIDATION_LINK,'Invalid verification link.', 403,'You send an invalid verification link');
        }

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::success(null, 'Email already verified.');
        }

        $user->markEmailAsVerified();

        return ApiResponse::success(null, 'Email verified successfully.');
    }

    public function resendVerificationEmail(Request $request)
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return ApiResponse::error(ErrorCode::EMAIL_ALREADY_VERIFIED,'Email already verified.', 400,'Your email already verified');
        }

        $user->sendEmailVerificationNotification();

        return ApiResponse::success(null, 'A new verification link has been sent.');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();

        // Revoke the current token
        $user->currentAccessToken()->delete();

        // Create a new token
        $tokenInstance = $user->createToken('auth_token');
        $newToken = $tokenInstance->plainTextToken;

        $expiration = config('sanctum.expiration');
        $expiresAt = $expiration ? now()->addMinutes($expiration)->toIso8601String() : null;

        return ApiResponse::success([
            'user' => UserResource::make($user->load(['roles', 'permissions'])),
            'token' => $newToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt,
        ], 'Successfully refreshed token.');
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request, SendPasswordResetLinkAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return match ($result['status']) {
            \App\Enums\Auth\PasswordResetStatus::INVALID_USER => ApiResponse::error(ErrorCode::USER_NOT_FOUND, 'User not found.', 404),
            \App\Enums\Auth\PasswordResetStatus::LINK_SENT => ApiResponse::success(null, __('passwords.sent')),
        };
    }

    /**
     * Reset password.
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        return match ($result['status']) {
            \App\Enums\Auth\PasswordResetStatus::INVALID_TOKEN => ApiResponse::error(ErrorCode::INVALID_VALIDATION_LINK, 'Invalid or expired password reset token.', 400),
            \App\Enums\Auth\PasswordResetStatus::RESET_SUCCESS => ApiResponse::success(null, __('passwords.reset')),
        };
    }
}
