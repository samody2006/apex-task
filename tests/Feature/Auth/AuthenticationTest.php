<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class AuthenticationTest extends TestCase
{
    use WithFaker;


    /** @test */
    public function register_with_valid_data(): void
    {
        $userData = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'role' => 'user'
        ];

        $response = $this->postJson('/api/register', $userData);

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

        $response->assertJsonFragment([
            'message' => 'User created successfully'
        ]);
    }

    /**
     * A basic feature test example.
     */

    public function test_User_Login(): void
    {
        $user = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password'),
        ]);

        $loginData = [
            'email' => 'user1@example.com',
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
     * A basic feature test example.
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
