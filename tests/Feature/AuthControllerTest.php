<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private string $registerEndpoint = '/api/auth/register';
    private string $loginEndpoint = '/api/auth/login';
    private string $logoutEndpoint = '/api/auth/logout';
    private string $userEndpoint = '/api/auth/user';

    /**
     * Registration Tests
     */
    public function test_user_can_register(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->postJson($this->registerEndpoint, $userData);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Usuario registrado com sucesso')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'token',
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue(Hash::check('Password123!', $user->password));
    }

    public function test_registration_validation(): void
    {
        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $existingEmailResponse = $this->postJson($this->registerEndpoint, [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $existingEmailResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $invalidEmailResponse = $this->postJson($this->registerEndpoint, [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $invalidEmailResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $shortPasswordResponse = $this->postJson($this->registerEndpoint, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $shortPasswordResponse->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $mismatchedPasswordResponse = $this->postJson($this->registerEndpoint, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword123!',
        ]);

        $mismatchedPasswordResponse->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /**
     * Login Tests
     */
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson($this->loginEndpoint, [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Login realizado com sucesso')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
                'token',
            ]);
    }

    public function test_login_validation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $invalidPasswordResponse = $this->postJson($this->loginEndpoint, [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $invalidPasswordResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $nonexistentEmailResponse = $this->postJson($this->loginEndpoint, [
            'email' => 'nonexistent@example.com',
            'password' => 'Password123!',
        ]);

        $nonexistentEmailResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $missingCredentialsResponse = $this->postJson($this->loginEndpoint, []);

        $missingCredentialsResponse->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Logout Tests
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson($this->logoutEndpoint);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Usuario deslogado com sucesso');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_unauthenticated_user_cannot_logout(): void
    {
        $response = $this->postJson($this->logoutEndpoint);

        $response->assertStatus(401);
    }

    /**
     * User Profile Tests
     */
    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson($this->userEndpoint);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.name', $user->name);
    }

    public function test_admin_flag_in_profile(): void
    {
        $adminUser = User::factory()->admin()->create();
        $regularUser = User::factory()->create();

        Sanctum::actingAs($adminUser);

        $adminResponse = $this->getJson($this->userEndpoint);

        $adminResponse->assertStatus(200)
            ->assertJsonPath('data.is_admin', true);

        Sanctum::actingAs($regularUser);

        $regularResponse = $this->getJson($this->userEndpoint);

        $regularResponse->assertStatus(200)
            ->assertJsonPath('data.is_admin', false);
    }

    /**
     * General Auth Tests
     */
    public function test_multiple_tokens_for_same_user(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('token-1')->plainTextToken;
        $token2 = $user->createToken('token-2')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token1}")
            ->getJson($this->userEndpoint)
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$token2}")
            ->getJson($this->userEndpoint)
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$token1}")
            ->postJson($this->logoutEndpoint)
            ->assertStatus(200);

        $this->withHeader('Authorization', "Bearer {$token2}")
            ->getJson($this->userEndpoint)
            ->assertStatus(200);
    }
}
