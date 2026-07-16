<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_issue_a_sanctum_api_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => UserRole::SuperAdmin,
        ]);

        $response = $this->postJson('/api/tokens', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'Back Office iPad',
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure(['token', 'token_type']);

        $this->assertDatabaseCount((new PersonalAccessToken())->getTable(), 1);
    }

    public function test_api_token_request_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response = $this->postJson('/api/tokens', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
            'device_name' => 'Back Office iPad',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertDatabaseCount((new PersonalAccessToken())->getTable(), 0);
    }
}