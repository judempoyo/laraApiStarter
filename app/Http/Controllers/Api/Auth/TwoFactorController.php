<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\TwoFactor\ConfirmTwoFactorAction;
use App\Actions\Auth\TwoFactor\DisableTwoFactorAction;
use App\Actions\Auth\TwoFactor\EnableTwoFactorAction;
use App\Actions\Auth\TwoFactor\RegenerateTwoFactorRecoveryCodesAction;
use App\Actions\Auth\TwoFactor\ResetTwoFactorViaSignedLinkAction;
use App\Actions\Auth\TwoFactor\ResetTwoFactorWithRecoveryCodeAction;
use App\Actions\Auth\TwoFactor\SendTwoFactorResetLinkAction;
use App\Actions\Auth\TwoFactor\ShowTwoFactorRecoveryCodesAction;
use App\Actions\Auth\TwoFactor\VerifyTwoFactorAction;
use App\Enums\AuthStatus;
use App\Enums\ErrorCode;
use App\Enums\Result\Auth\TwoFactorResult;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactor\ConfirmTwoFactorRequest;
use App\Http\Requests\Auth\TwoFactor\DisableTwoFactorRequest;
use App\Http\Requests\Auth\TwoFactor\RegenerateTwoFactorRecoveryCodesRequest;
use App\Http\Requests\Auth\TwoFactor\ResetTwoFactorViaLinkRequest;
use App\Http\Requests\Auth\TwoFactor\ResetTwoFactorWithRecoveryCodeRequest;
use App\Http\Requests\Auth\TwoFactor\SendTwoFactorResetLinkRequest;
use App\Http\Requests\Auth\TwoFactor\ShowTwoFactorRecoveryCodesRequest;
use App\Http\Requests\Auth\TwoFactor\VerifyTwoFactorRequest;
use App\Http\Resources\AuthResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    /**
     * Initiate 2FA setup — returns the secret and QR code URI.
     */
    public function enable(Request $request, EnableTwoFactorAction $action): JsonResponse
    {
        $result = $action->execute($request->user());

        return match ($result['status']) {
            TwoFactorResult::ALREADY_ENABLED => throw new ApiException(
                errorCode: ErrorCode::TWO_FACTOR_ALREADY_ENABLED,
                message: __('api.2fa_already_enabled'),
                statusCode: 409,
                userMessage: __('api.2fa_already_enabled_msg'),
            ),
            TwoFactorResult::ENABLED => ApiResponse::success([
                'secret'      => $result['secret'],
                'qr_code_uri' => $result['qr_code_uri'],
            ], __('api.2fa_setup')),
        };
    }

    /**
     * Confirm 2FA activation by submitting the first TOTP code.
     * Generates and returns 8 one-time recovery codes.
     */
    public function confirm(ConfirmTwoFactorRequest $request, ConfirmTwoFactorAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->input('code'));

        return match ($result['status']) {
            TwoFactorResult::ALREADY_ENABLED => throw new ApiException(
                errorCode: ErrorCode::TWO_FACTOR_ALREADY_ENABLED,
                message: __('api.2fa_already_enabled'),
                statusCode: 409,
                userMessage: __('api.2fa_already_enabled_msg'),
            ),
            TwoFactorResult::INVALID_CODE => throw ApiException::unprocessable(
                __('api.2fa_invalid_code'),
                ErrorCode::TWO_FACTOR_INVALID_CODE
            ),
            TwoFactorResult::CONFIRMED => ApiResponse::success([
                'recovery_codes' => $result['recovery_codes'] ?? [],
            ], __('api.2fa_confirmed')),
        };
    }

    /**
     * Verify a TOTP code or recovery code during the login flow.
     * On success, revokes temporary token and returns a full authentication session token.
     */
    public function verify(VerifyTwoFactorRequest $request, VerifyTwoFactorAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->input('code'));

        return match ($result['status']) {
            TwoFactorResult::NOT_ENABLED => throw ApiException::unprocessable(
                __('api.2fa_not_enabled'),
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::NOT_CONFIRMED => throw ApiException::unprocessable(
                __('api.2fa_not_confirmed'),
                ErrorCode::TWO_FACTOR_NOT_CONFIRMED
            ),
            TwoFactorResult::INVALID_CODE => throw ApiException::unprocessable(
                __('api.2fa_invalid_code'),
                ErrorCode::TWO_FACTOR_INVALID_CODE
            ),
            TwoFactorResult::VERIFIED => ApiResponse::success([
                'auth_status'         => AuthStatus::AUTHENTICATED->value,
                'two_factor_required' => false,
                'user'                => AuthResource::make($result['user']),
                'token'               => $result['token'],
                'token_type'          => $result['token_type'],
                'expires_at'          => $result['expires_at'],
            ], __('api.2fa_verified')),
        };
    }

    /**
     * View existing recovery codes (requires current password).
     */
    public function showRecoveryCodes(
        ShowTwoFactorRecoveryCodesRequest $request,
        ShowTwoFactorRecoveryCodesAction $action
    ): JsonResponse {
        $result = $action->execute($request->user(), $request->input('password'));

        return match ($result['status']) {
            TwoFactorResult::NOT_ENABLED => throw ApiException::unprocessable(
                __('api.2fa_not_enabled'),
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::INVALID_PASSWORD => throw ApiException::unprocessable(
                __('api.2fa_invalid_password'),
                ErrorCode::PASSWORD_MISMATCH
            ),
            TwoFactorResult::RECOVERY_CODES_RETRIEVED => ApiResponse::success([
                'recovery_codes' => $result['recovery_codes'],
            ], __('api.2fa_recovery_codes_retrieved') ?: 'Recovery codes retrieved successfully.'),
        };
    }

    /**
     * Regenerate fresh recovery codes (requires current password).
     */
    public function regenerateRecoveryCodes(
        RegenerateTwoFactorRecoveryCodesRequest $request,
        RegenerateTwoFactorRecoveryCodesAction $action
    ): JsonResponse {
        $result = $action->execute($request->user(), $request->input('password'));

        return match ($result['status']) {
            TwoFactorResult::NOT_ENABLED => throw ApiException::unprocessable(
                __('api.2fa_not_enabled'),
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::INVALID_PASSWORD => throw ApiException::unprocessable(
                __('api.2fa_invalid_password'),
                ErrorCode::PASSWORD_MISMATCH
            ),
            TwoFactorResult::RECOVERY_CODES_REGENERATED => ApiResponse::success([
                'recovery_codes' => $result['recovery_codes'],
            ], __('api.2fa_recovery_codes_regenerated') ?: 'Recovery codes regenerated successfully.'),
        };
    }

    /**
     * Reset 2FA using a recovery code and password (can be used when locked out).
     */
    public function resetWithRecoveryCode(
        ResetTwoFactorWithRecoveryCodeRequest $request,
        ResetTwoFactorWithRecoveryCodeAction $action
    ): JsonResponse {
        $email = $request->input('email') ?? $request->user()?->email;

        $result = $action->execute(
            (string) $email,
            $request->input('password'),
            $request->input('recovery_code')
        );

        return match ($result['status']) {
            TwoFactorResult::NOT_ENABLED => throw ApiException::unprocessable(
                __('api.2fa_not_enabled'),
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::INVALID_PASSWORD => throw ApiException::unprocessable(
                __('api.2fa_invalid_password'),
                ErrorCode::PASSWORD_MISMATCH
            ),
            TwoFactorResult::INVALID_RECOVERY_CODE => throw ApiException::unprocessable(
                __('api.2fa_invalid_recovery_code') ?: 'The provided recovery code is invalid or has already been used.',
                ErrorCode::TWO_FACTOR_INVALID_RECOVERY_CODE
            ),
            TwoFactorResult::RESET_SUCCESS => ApiResponse::success(
                null,
                __('api.2fa_reset_success') ?: 'Two-factor authentication has been successfully reset. You may now log in with your credentials.'
            ),
        };
    }

    /**
     * Send a secure 2FA reset link to the user's registered email address.
     */
    public function sendResetLink(
        SendTwoFactorResetLinkRequest $request,
        SendTwoFactorResetLinkAction $action
    ): JsonResponse {
        $result = $action->execute($request->validated('email'));

        return ApiResponse::success(null, $result['message']);
    }

    /**
     * Complete 2FA reset via a temporary signed email link.
     */
    public function resetViaSignedLink(
        ResetTwoFactorViaLinkRequest $request,
        string $id,
        string $hash,
        ResetTwoFactorViaSignedLinkAction $action
    ): JsonResponse {
        $user = User::find($id);

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return ApiResponse::error(
                ErrorCode::FORBIDDEN,
                __('api.2fa_reset_link_invalid') ?: 'The reset link is invalid or has expired.',
                403
            );
        }

        $result = $action->execute($user, $request->validated('password'));

        return match ($result['status']) {
            TwoFactorResult::NOT_ENABLED => throw ApiException::unprocessable(
                __('api.2fa_not_enabled'),
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::INVALID_PASSWORD => throw ApiException::unprocessable(
                __('api.2fa_invalid_password'),
                ErrorCode::PASSWORD_MISMATCH
            ),
            TwoFactorResult::RESET_SUCCESS => ApiResponse::success(
                null,
                __('api.2fa_reset_success') ?: 'Two-factor authentication has been successfully reset. You can now log in.'
            ),
        };
    }

    /**
     * Disable 2FA (requires current password confirmation).
     */
    public function disable(DisableTwoFactorRequest $request, DisableTwoFactorAction $action): JsonResponse
    {
        $result = $action->execute($request->user(), $request->input('password'));

        return match ($result['status']) {
            TwoFactorResult::NOT_ENABLED => throw ApiException::unprocessable(
                __('api.2fa_not_enabled'),
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::INVALID_PASSWORD => throw ApiException::unprocessable(
                __('api.2fa_invalid_password'),
                ErrorCode::PASSWORD_MISMATCH
            ),
            TwoFactorResult::DISABLED => ApiResponse::noContent(__('api.2fa_disabled')),
        };
    }
}
