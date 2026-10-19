<?php

declare(strict_types=1);

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Enums\Result\Auth\PasswordResetResult;
use App\Models\User;

it('sends password reset link', function () {
    User::factory()->create([
        'email' => 'test@example.com',
    ]);

    $action = app(SendPasswordResetLinkAction::class);

    $result = $action->execute([
        'email' => 'test@example.com',
    ]);

    expect($result['status'])->toBe(PasswordResetResult::LINK_SENT);
});
