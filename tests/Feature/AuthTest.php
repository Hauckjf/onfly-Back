<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Str;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test_'.Str::random(8).'@example.com',
            'password' => bcrypt($password = 'Un1qu3P@ss_'.time())
        ])->assignRole('user');

        // Faz login para obter token
        $response = $this->postJson('/api/auth/login', [
            'email' => $this->user->email,
            'password' => $password
        ]);

        $this->token = $response->json('access_token');
    }

    public function test_register()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'new_'.Str::random(8).'@example.com',
            'password' => 'NewSecurePass123!@#',
            'password_confirmation' => 'NewSecurePass123!@#'
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]);
    }

    public function test_login()
    {

        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        $limiter->clear('login:'.request()->ip());

        $password = 'LoginP@ss_'.time();
        $user = User::factory()->create([
            'email' => 'login_test@example.com',
            'password' => bcrypt($password)
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'user'
            ]);
    }

    public function test_logout()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer '.$this->token
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout realizado com sucesso']);
    }

    public function test_invalid_login()
    {
        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        $limiter->clear('login:'.request()->ip());

        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid@example.com',
            'password' => 'InvalidP@ss123'
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Credenciais inválidas']);
    }

    public function test_rate_limiting_login()
    {
        $email = 'rate_'.Str::random(3).'@example.com';
        $validPassword = 'ValidPass123!@#';

        User::factory()->create([
            'email' => $email,
            'password' => bcrypt($validPassword)
        ]);

        $ip = '127.0.0.1';
        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        $limiter->clear('login:'.$ip);

        $maxAttempts = 5;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $response = $this->postJson('/api/auth/login', [
                'email' => $email,
                'password' => 'WrongPass123!@#'
            ]);
            $response->assertStatus(401);
        }

        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => 'WrongPass123!@#'
        ]);
        $response->assertStatus(429);
    }

}
