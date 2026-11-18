<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultUserSeeder extends Seeder
{
    /**
     * Seed the default administrator user.
     */
    public function run(): void
    {
        $email = (string) (config('api-starter.default_admin.email') ?: env('DEFAULT_ADMIN_EMAIL', 'admin@example.com'));
        $password = (string) (config('api-starter.default_admin.password') ?: env('DEFAULT_ADMIN_PASSWORD', 'password'));

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin User',
                'password' => Hash::make($password),
                'password_updated_at' => now(),
                'email_verified_at' => now(),
                'status' => UserStatus::ACTIVE,
            ]
        );

        // If Spatie Role model exists and method assignRole is present (e.g. RBAC module installed)
        if (class_exists(Role::class) && method_exists($user, 'assignRole')) {
            /** @var Role|null $adminRole */
            $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
            if ($adminRole) {
                $user->assignRole($adminRole);
            }
        }
    }
}
