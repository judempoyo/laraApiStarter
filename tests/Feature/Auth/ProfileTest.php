<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_update_their_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->putJson('/api/v1/auth/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_users_can_update_their_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->putJson('/api/v1/auth/profile/password', [
            'current_password' => 'old_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(200);

        $this->assertTrue(Hash::check('new_password', $user->fresh()->password));
    }

    public function test_users_cannot_update_password_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password'),
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->putJson('/api/v1/auth/profile/password', [
            'current_password' => 'wrong_password',
            'password' => 'new_password',
            'password_confirmation' => 'new_password',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_users_can_refresh_their_token()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
        ])->postJson('/api/v1/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                ],
            ]);

        // Old token should be revoked
        $this->assertCount(1, $user->fresh()->tokens);
    }
}
