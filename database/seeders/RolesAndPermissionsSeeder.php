<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'rooms.disable',
            'profile.update',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'api');
        }

        // Create roles and assign created permissions

        // Partner Role
        $partnerRole = Role::findOrCreate('Partner', 'api');
        $partnerRole->givePermissionTo(Permission::whereIn('name', ['rooms.disable', 'profile.update'])->where('guard_name', 'api')->get());

        // Admin Role
        $adminRole = Role::findOrCreate('Admin', 'api');
        $adminRole->givePermissionTo(Permission::all());

        // Standard User Role
        $userRole = Role::findOrCreate('User', 'api');
        $userRole->givePermissionTo(Permission::where('name', 'profile.update')->where('guard_name', 'api')->first());

        // Create initial Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'password_updated_at' => now(),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole($adminRole);

        // Create initial Partner User
        $partner = User::create([
            'name' => 'Partner User',
            'email' => 'partner@test.com',
            'password' => bcrypt('password'),
            'password_updated_at' => now(),
            'email_verified_at' => now(),
        ]);
        $partner->assignRole($partnerRole);
    }
}
