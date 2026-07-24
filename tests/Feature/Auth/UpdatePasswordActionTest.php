<?php

use App\Actions\Auth\UpdatePasswordAction;
use App\DTOs\Auth\UpdatePasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates password when current password is valid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old_password'),
    ]);

    $dto = new UpdatePasswordDTO(
        currentPassword: 'old_password',
        newPassword: 'new_password'
    );

    $action = app(UpdatePasswordAction::class);

    $result = $action->execute($user, $dto);
    
    expect($result['status'])->toBe(\App\Enums\Result\UpdatePasswordResult::SUCCESS);
    expect(Hash::check('new_password', $user->fresh()->password))->toBeTrue();
});

it('fails password update when current password is invalid', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old_password'),
    ]);

    $dto = new UpdatePasswordDTO(
        currentPassword: 'wrong',
        newPassword: 'new_password'
    );

    $action = app(UpdatePasswordAction::class);

    $result = $action->execute($user, $dto);

    expect($result['status'])->toBe(\App\Enums\Result\UpdatePasswordResult::INVALID_CURRENT_PASSWORD);
});
