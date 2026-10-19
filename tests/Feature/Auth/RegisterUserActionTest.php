<?php

declare(strict_types=1);

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\RegisterDTO;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

it('registers a new user', function () {
    $dto = new RegisterDTO(
        name: 'Test User',
        email: 'test@example.com',
        password: 'password'
    );

    $action = app(RegisterUserAction::class);

    $result = $action->execute($dto);

    expect($result)->toBeArray();
    expect($result['user'])->toBeInstanceOf(User::class);

    expect(
        User::where('email', 'test@example.com')->exists()
    )->toBeTrue();
});
