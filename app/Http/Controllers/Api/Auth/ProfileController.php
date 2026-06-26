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
            \App\Enums\Result\Auth\UpdateProfileResult::SUCCESS =>
            ApiResponse::success(UserResource::make($result['user']), 'Profile updated successfully.'),
        };
    }

    /**
     * change the user email
     */
    public function changeEmail(UpdateEmailRequest $request, UpdateEmailAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            UpdateEmailDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            \App\Enums\Result\Auth\UpdateEmailResult::SUCCESS =>
            ApiResponse::success(UserResource::make($result['user']), 'User email changed successfully, new verification email has been sent.'),
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
            \App\Enums\Result\Auth\UpdatePasswordResult::SUCCESS                  =>
            ApiResponse::success(null, 'Password updated successfully.'),

            \App\Enums\Result\Auth\UpdatePasswordResult::INVALID_CURRENT_PASSWORD =>
            ApiResponse::error(
                ErrorCode::PASSWORD_MISMATCH,
                'The provided password does not match your current password.',
                422,
                'password mismatch'
            ),
        };
    }
}
