<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Enums\ErrorCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request, RegisterUserAction $action): JsonResponse
    {
         $result = $action->execute(
        RegisterDTO::fromRequest($request->validated())
    );


        return ApiResponse::success([
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
        ], 'User registered successfully. Please verify your email.', 201);
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request, LoginUserAction $action): JsonResponse
    {
        $result = $action->execute(LoginDTO::fromRequest($request->validated()));

        return ApiResponse::success([
            'user' => UserResource::make($result['user']),
            'token' => $result['token'],
        ], 'Login successful.');
    }

    /**
     * Logout user (revoke the token).
     */
public function logout(Request $request): JsonResponse
{
    $request->user()->currentAccessToken()->delete();

    return ApiResponse::success(null, 'Logged out successfully.');
}

    /**
     * Get the authenticated user.
     */
    public function user(Request $request): JsonResponse
    {
        return ApiResponse::success(UserResource::make($request->user()));
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
        $user->currentAccessToken()->delete();

        $newToken = $user->createToken('auth_token', ['api'])->plainTextToken;

        return ApiResponse::success([
            'user' => UserResource::make($user),
            'token' => $newToken,
            'token_type' => 'Bearer',
        ], 'Successfully refreshed token.');
    }
}
