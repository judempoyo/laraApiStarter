<?php

use App\Actions\Auth\RegisterUserAction;
use App\DTOs\Auth\RegisterDTO;
use App\Models\User;
use Illuminate\Support\Facades\DB;


beforeEach(function () {
    $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
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
