<?php

namespace Tests\Feature;

use App\Models\Trip;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TripTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $adm;
    protected $token;
    protected $trip;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);

        $ip = '127.0.0.1';
        $limiter = app(\Illuminate\Cache\RateLimiter::class);
        $limiter->clear('login:'.$ip);

        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => bcrypt('SecurePass123!@#')
        ])->assignRole('user');

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'SecurePass123!@#'
        ]);

        $this->token = $response->json('access_token');

        $this->trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'destination' => 'Test Destination',
            'startDate' => now()->addDays(1)->format('Y-m-d'),
            'endDate' => now()->addDays(5)->format('Y-m-d'),
            'status' => 'solicitado'
        ]);
    }

    public function test_index_trips()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/trips');

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_show_trip()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/trips/' . $this->trip->id);

        $response->assertStatus(200)
            ->assertJson(['data' => ['id' => $this->trip->id]]);
    }

    public function test_store_trip()
    {
        $tripData = [
            'destination' => 'New Trip Destination',
            'startDate' => now()->addDays(2)->format('Y-m-d'),
            'endDate' => now()->addDays(7)->format('Y-m-d')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/trips', $tripData);

        $response->assertStatus(201)
            ->assertJson(['data' => ['destination' => 'New Trip Destination']]);
    }

    public function test_update_trip()
    {
        $updateData = [
            'destination' => 'New Trip Destination',
            'startDate' => now()->addDays(2)->format('Y-m-d'),
            'endDate' => now()->addDays(7)->format('Y-m-d')
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/trips/' . $this->trip->id, $updateData);

        $response->assertStatus(200);
    }

    public function test_update_trip_status()
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('AdminPass123!@#')
        ])->assignRole('admin');

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'AdminPass123!@#'
        ]);

        $adminToken = $login->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $adminToken
        ])->putJson('/api/trips/' . $this->trip->id . '/status', [
            'status' => 'confirmado'
        ]);

        $response->assertStatus(200)
            ->assertJson(['data' => ['status' => 'confirmado']]);
    }

    public function test_unauthorized_update_status()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson('/api/trips/' . $this->trip->id . '/status', [
            'status' => 'confirmado'
        ]);

        $response->assertStatus(403);
    }
}
