<?php

declare(strict_types=1);

use App\Models\User;

it('soft deletes a user and retains database record', function (): void {
    $user = User::factory()->create();

    $userId = $user->id;
    $user->delete();

    expect(User::find($userId))->toBeNull();

    $trashed = User::withTrashed()->find($userId);
    expect($trashed)->not->toBeNull();
    expect($trashed->deleted_at)->not->toBeNull();

    $trashed->restore();
    expect(User::find($userId))->not->toBeNull();
});
