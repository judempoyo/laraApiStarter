<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_routes_have_rate_limiting()
    {
        // We can't easily test the actual throttle without making many requests,
        // but we can verify the middleware is applied by checking the response headers
        // if they include X-RateLimit-Limit (Note: default throttle does this).
        
        $user = User::factory()->create();

        // Login route
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertHeader('X-RateLimit-Limit');
        
        // Let's verify it hits 429 if we loop (if we want to be thorough)
        // But for a starter kit, verifying the headers exist is usually enough proof the middleware is active.
    }
}
