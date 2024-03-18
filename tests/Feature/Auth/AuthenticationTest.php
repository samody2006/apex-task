<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class AuthenticationTest extends TestCase
{
    use WithFaker;

    /**
     * Test register a new user with valid data
     *
     * @return void
     */
    public function register_with_valid_data(): void
    {
        $user = User::factory()->create();
        $response = $this->postJson('/api/register', $user);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at'
                    ],
                    'token',
                    'message'
                ]
            ]);

    }
    /**
     * Test register a new user - Validation Error.
     *
     * @return void
     */
    public function test_register_with_validation_error(): void
    {
        $userData = [
            'name' => '', // Intentionally making the name field empty to trigger a validation error
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'user'
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Test login  user with valid data
     *
     * @return void
     */

    public function test_User_Login(): void
    {
        $user = User::factory()->create([
            'email' => 'user2@example.com',
            'password' => Hash::make('password'),
        ]);

        $loginData = [
            'email' => 'user2@example.com',
            'password' => 'password',
        ];

        $response = $this->postJson('/api/login', $loginData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at'
                    ],
                    'token',
                    'message'
                ]
            ]);
    }
    /**
     * Test login a user with invalid data
     *
     * @return void
     */
 public function test_user_login_with_invalid_data(): void
    {
        $user = User::factory()->create([
            'email' => 'user4@example.com',
            'password' => Hash::make('password'),
        ]);

        $invalidLoginData = [
            'email' => 'user4@example.com',
            'password' => 'invalidpassword',
        ];
        $response = $this->postJson('/api/login', $invalidLoginData);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }


    /**
     * Test logout user
     *
     * @return void
     */
    public function test_logout_user(): void
    {
        $response = $this->post('/api/logout');
        $response->assertStatus(200)
            ->assertJson([
                "status" => true,
                "message" => "User is logout"
            ]);
    }




}
