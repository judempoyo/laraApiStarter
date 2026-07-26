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
                message: 'Two-factor authentication is already enabled.',
                statusCode: 409,
                userMessage: 'Two-factor authentication is already active on this account.',
            ),
            TwoFactorResult::ENABLED => ApiResponse::success([
                'secret'      => $result['secret'],
                'qr_code_uri' => $result['qr_code_uri'],
            ], 'Scan the QR code with your authenticator app, then confirm with the generated code.'),
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
                message: 'Two-factor authentication is already enabled.',
                statusCode: 409,
                userMessage: 'Two-factor authentication is already active on this account.',
            ),
            TwoFactorResult::INVALID_CODE => throw ApiException::unprocessable(
                'The provided code is invalid or expired.',
                ErrorCode::TWO_FACTOR_INVALID_CODE
            ),
            TwoFactorResult::CONFIRMED => ApiResponse::success(null, 'Two-factor authentication has been enabled.'),
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
                'Two-factor authentication is not enabled.',
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::NOT_CONFIRMED => throw ApiException::unprocessable(
                'Two-factor authentication has not been confirmed yet.',
                ErrorCode::TWO_FACTOR_NOT_CONFIRMED
            ),
            TwoFactorResult::INVALID_CODE => throw ApiException::unprocessable(
                'The provided code is invalid or expired.',
                ErrorCode::TWO_FACTOR_INVALID_CODE
            ),
            TwoFactorResult::VERIFIED => ApiResponse::success(null, 'Two-factor code verified successfully.'),
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
                'Two-factor authentication is not enabled.',
                ErrorCode::TWO_FACTOR_NOT_ENABLED
            ),
            TwoFactorResult::INVALID_PASSWORD => throw ApiException::unprocessable(
                'The provided password is incorrect.',
                ErrorCode::PASSWORD_MISMATCH
            ),
            TwoFactorResult::DISABLED => ApiResponse::noContent('Two-factor authentication has been disabled.'),
        };
    }
}
