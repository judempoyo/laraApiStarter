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
        $user = $action->execute(
            $request->user(),
            UpdateProfileDTO::fromRequest($request->validated())
        );

        return ApiResponse::success(UserResource::make($user), 'Profile updated successfully.');
    }

    /**
     * Update the user password.
     */
    public function updatePassword(UpdatePasswordRequest $request, UpdatePasswordAction $action): JsonResponse
    {
        $action->execute(
            $request->user(),
            UpdatePasswordDTO::fromRequest($request->validated())
        );

        return ApiResponse::success(null, 'Password updated successfully.');
    }
}
