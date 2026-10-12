<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Events\ActivityRecorded;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadAvatarAction
{
    public function execute(User $user, UploadedFile $file): User
    {
        // Delete previous local avatar
        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $file->store('avatars/'.$user->id, 'public');

        $user->update(['avatar' => $path]);

        ActivityRecorded::dispatch('auth.avatar.uploaded', 'Avatar mis à jour.', $user->id);

        return $user->fresh();
    }
}
