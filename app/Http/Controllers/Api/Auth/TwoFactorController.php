<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\TwoFactor\ConfirmTwoFactorAction;
use App\Actions\Auth\TwoFactor\DisableTwoFactorAction;
use App\Actions\Auth\TwoFactor\EnableTwoFactorAction;
use App\Actions\Auth\TwoFactor\VerifyTwoFactorAction;
use App\Enums\ErrorCode;
use App\Enums\Result\Auth\TwoFactorResult;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\TwoFactor\ConfirmTwoFactorRequest;
use App\Http\Requests\Auth\TwoFactor\VerifyTwoFactorRequest;
use App\Http\Requests\Auth\TwoFactor\DisableTwoFactorRequest;

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
            TwoFactorResult::CONFIRMED => ApiResponse::success(null, __('api.2fa_confirmed')),
        };
    }

    /**
     * Verify a TOTP code (login flow).
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
            TwoFactorResult::VERIFIED => ApiResponse::success(null, __('api.2fa_verified')),
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
