<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\DefaultUserSeeder;
use Illuminate\Support\Facades\Hash;

it('seeds the default admin user using configuration or environment variables', function (): void {
    config([
        'api-starter.default_admin.email' => 'admin_test@starter.test',
        'api-starter.default_admin.password' => 'secret_password_123',
    ]);

    $this->seed(DefaultUserSeeder::class);

    $user = User::where('email', 'admin_test@starter.test')->first();

    expect($user)->not->toBeNull();
    expect($user->name)->toBe('Admin User');
    expect($user->status)->toBe(UserStatus::ACTIVE);
    expect(Hash::check('secret_password_123', $user->password))->toBeTrue();
    expect($user->email_verified_at)->not->toBeNull();
});

it('is idempotent when run multiple times', function (): void {
    config([
        'api-starter.default_admin.email' => 'admin_test@starter.test',
        'api-starter.default_admin.password' => 'secret_password_123',
    ]);

    $this->seed(DefaultUserSeeder::class);
    $this->seed(DefaultUserSeeder::class);

    $count = User::where('email', 'admin_test@starter.test')->count();
    expect($count)->toBe(1);
});
