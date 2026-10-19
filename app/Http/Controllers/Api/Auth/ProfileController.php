<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\UpdateEmailAction;
use App\Actions\Auth\UpdatePasswordAction;
use App\Actions\Auth\UpdateProfileAction;
use App\DTOs\Auth\UpdateEmailDTO;
use App\DTOs\Auth\UpdatePasswordDTO;
use App\DTOs\Auth\UpdateProfileDTO;
use App\Enums\ErrorCode;
use App\Enums\Result\Auth\UpdateEmailResult;
use App\Enums\Result\Auth\UpdatePasswordResult;
use App\Enums\Result\Auth\UpdateProfileResult;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateEmailRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    /**
     * Update the user profile.
     */
    public function update(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            UpdateProfileDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            UpdateProfileResult::SUCCESS => ApiResponse::success(UserResource::make($result['user']), __('api.profile_updated')),
        };
    }

    /**
     * Change the user email.
     */
    public function changeEmail(UpdateEmailRequest $request, UpdateEmailAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            UpdateEmailDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            UpdateEmailResult::SUCCESS => ApiResponse::success(UserResource::make($result['user']), __('api.email_changed')),
        };
    }

    /**
     * Update the user password.
     */
    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            UpdatePasswordDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            UpdatePasswordResult::SUCCESS => ApiResponse::noContent(__('api.password_updated')),

            UpdatePasswordResult::INVALID_CURRENT_PASSWORD => throw ApiException::unprocessable(
                __('api.wrong_current_password'),
                ErrorCode::PASSWORD_MISMATCH
            ),
        };
    }

    /**
     * Upload or replace the user avatar.
     */

    /**
     * Delete the user avatar.
     */
}
