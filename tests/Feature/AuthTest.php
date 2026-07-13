<?php

namespace Tests\Feature;

use App\Models\UserDms;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear rate limiter before each test
        RateLimiter::clear('login-attempts:127.0.0.1|admin@dms.local');
    }

    public function test_user_can_login_with_correct_credentials()
    {
        // Create user
        $user = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dms.local',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'role',
                    'email',
                ]
            ]);

        $this->assertEquals('admin', $response->json('data.role'));
        $this->assertEquals('admin@dms.local', $response->json('data.email'));
    }

    public function test_user_cannot_login_with_incorrect_password()
    {
        $user = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dms.local',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'INVALID_CREDENTIALS',
                    'message' => 'Email atau password salah',
                ]
            ]);
    }

    public function test_login_attempts_are_rate_limited()
    {
        $user = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Attempt 5 failed logins
        for ($i = 0; $i < 5; $i++) {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'admin@dms.local',
                'password' => 'wrong-password',
            ]);
            $response->assertStatus(401);
        }

        // 6th attempt should be locked out
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dms.local',
            'password' => 'password', // even with correct password
        ]);

        $response->assertStatus(429)
            ->assertJsonStructure([
                'success',
                'error' => [
                    'code',
                    'message',
                ]
            ]);
        
        $this->assertEquals('LOGIN_LOCKED', $response->json('error.code'));
    }

    public function test_authenticated_user_can_logout()
    {
        $user = UserDms::create([
            'email' => 'admin@dms.local',
            'password' => 'password',
            'role' => 'admin',
        ]);

        // Login first to get token
        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@dms.local',
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        // Logout request
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
    }
}
