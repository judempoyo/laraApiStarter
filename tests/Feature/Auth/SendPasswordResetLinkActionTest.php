<?php

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Models\User;
use Illuminate\Support\Facades\Password;

it('sends password reset link', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = app(SendPasswordResetLinkAction::class);

    $result = $action->execute([
        'email' => 'test@example.com',
    ]);

    expect($result['status'])->toBe(\App\Enums\Result\Auth\PasswordResetResult::LINK_SENT);
});
