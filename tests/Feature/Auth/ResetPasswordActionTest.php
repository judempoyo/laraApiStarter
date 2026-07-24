<?php

use App\Actions\Auth\ResetPasswordAction;
use App\Models\User;
use Illuminate\Support\Facades\Password;

it('resets password successfully', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $token = Password::createToken($user);

    $action = app(ResetPasswordAction::class);

    $result = $action->execute([
        'email'                 => $user->email,
        'password'              => 'new_password',
        'password_confirmation' => 'new_password',
        'token'                 => $token,
    ]);

    expect($result['status'])->toBe(\App\Enums\Result\PasswordResetResult::RESET_SUCCESS);
});
