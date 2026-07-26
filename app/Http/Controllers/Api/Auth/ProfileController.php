<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Actions\Auth\UpdateEmailAction;
use App\Actions\Auth\UpdatePasswordAction;
use App\Actions\Auth\UpdateProfileAction;
use App\Actions\Auth\UploadAvatarAction;
use App\DTOs\Auth\UpdateEmailDTO;
use App\DTOs\Auth\UpdatePasswordDTO;
use App\DTOs\Auth\UpdateProfileDTO;
use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdateEmailRequest;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * Change the user email.
     */
    public function changeEmail(UpdateEmailRequest $request, UpdateEmailAction $action): JsonResponse
    {
        $result = $action->execute(
            $request->user(),
            UpdateEmailDTO::fromRequest($request->validated())
        );

        return match ($result['status']) {
            \App\Enums\Result\Auth\UpdateEmailResult::SUCCESS =>
            ApiResponse::success(UserResource::make($result['user']), 'Email changed successfully. A new verification link has been sent.'),
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
            \App\Enums\Result\Auth\UpdatePasswordResult::SUCCESS =>
            ApiResponse::noContent('Password updated successfully.'),

            \App\Enums\Result\Auth\UpdatePasswordResult::INVALID_CURRENT_PASSWORD =>
            throw ApiException::unprocessable(
                'The provided password does not match your current password.',
                \App\Enums\ErrorCode::PASSWORD_MISMATCH
            ),
        };
    }

    /**
     * Upload or replace the user avatar.
     */
    public function uploadAvatar(UploadAvatarRequest $request, UploadAvatarAction $action): JsonResponse
    {
        $user = $action->execute($request->user(), $request->file('avatar'));

        return ApiResponse::success(
            ['avatar_url' => $user->avatar_url],
            'Profile picture updated.'
        );
    }

    /**
     * Delete the user avatar.
     */
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->avatar) {
            throw ApiException::notFound('Avatar');
        }

        if (! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return ApiResponse::noContent('Profile picture deleted.');
    }
}
