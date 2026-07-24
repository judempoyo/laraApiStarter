<?php

use App\Actions\Auth\UpdateProfileAction;
use App\DTOs\Auth\UpdateProfileDTO;
use App\Models\User;

it('updates user profile', function () {
    $user = User::factory()->create();

    $dto = new UpdateProfileDTO(
        name: 'New Name'
    );

    $action = app(UpdateProfileAction::class);

    $result = $action->execute($user, $dto);
    $updatedUser = $result['user'];

    expect($result['status'])->toBe(\App\Enums\Result\UpdateProfileResult::SUCCESS);
    expect($updatedUser->name)->toBe('New Name');
});
