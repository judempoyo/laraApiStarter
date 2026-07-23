<?php
namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_admin_can_access_admin_stats()
    {
        $admin = User::factory()->create();
        $admin->assignRole(UserRole::ADMIN->value);
        
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Admin stats access granted.']);
    }

    public function test_login_response_contains_roles_and_permissions()
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        if (! $admin->hasRole(UserRole::ADMIN->value)) {
            $admin->assignRole(UserRole::ADMIN->value);
        }

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.account_type', UserRole::ADMIN->value)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'account_type',
                    ],
                    'token',
                    'token_type',
                    'expires_at',
                ],
                'message',
            ]);
    }
}
