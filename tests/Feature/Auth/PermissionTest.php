<?php

namespace Tests\Feature\Auth;

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
        $admin = User::where('email', 'admin@test.com')->first();
        
        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/auth/admin/stats');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Admin stats access granted.']);
    }


    public function test_login_response_contains_roles_and_permissions()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.roles.0', 'Admin')
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'roles',
                        'permissions',
                    ]
                ]
            ]);
    }
}
