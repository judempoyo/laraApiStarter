<?php

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\UpdatePasswordAction;
use App\Actions\Auth\UpdateProfileAction;
use App\DTOs\Auth\UpdatePasswordDTO;
use App\DTOs\Auth\UpdateProfileDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
            \App\Enums\Auth\ProfileStatus::SUCCESS => ApiResponse::success(
                UserResource::make($result['data']['user']),
                'Profile updated successfully.'
            ),
            default => ApiResponse::error(\App\Enums\ErrorCode::SERVER_ERROR, 'Failed to update profile.', 500)
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
            \App\Enums\Auth\ProfileStatus::INVALID_CURRENT_PASSWORD => ApiResponse::error(\App\Enums\ErrorCode::PASSWORD_MISMATCH, 'The provided password does not match your current password.', 400),
            \App\Enums\Auth\ProfileStatus::SUCCESS => ApiResponse::success(null, 'Password updated successfully.'),
        };
    }
}
