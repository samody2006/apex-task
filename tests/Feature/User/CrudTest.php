<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class CrudTest extends TestCase
{
    use WithFaker;

    /**
     * Test showing all users.
     *
     * @return void
     */

    public function test_show_all_users(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->get('api/users');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'users' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Test creating a user.
     *
     * @return void
     */
    public function test_create_new_user(): void
    {
        // Creating a user with the factory
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->post('api/users', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password',
            'role' => 'user'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'created_at'
                ]
            ]
        ]);
    }

    /**
     * Test creating a new user - Validation Error.
     *
     * @return void
     */
    public function test_create_new_user_validation_error(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        $response = $this->post('api/users', [
            'name' => '',
            'email' => 'john.john@example.com',
            'password' => 'password',
            'role' => 'user'
        ]);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Test showing a user by id.
     *
     * @return void
     */
    public function test_show_user_by_id(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->get('api/users/' . $user->id);

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
                    ]
                ]
            ]);
    }


    /**
     * Test showing a user by id with invalid id for User not found error.
     *
     * @return void
     */
    public function test_show_user_by_id_with_invalid_id(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->get('api/users/' . 444);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Test updating a user.
     *
     * @return void
     */
    public function test_update_user(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->put('api/users/' . $user->id, [
            'name' => 'Updated Name',
            'password' => 'updatedpassword',
            'role' => 'admin'
        ]);

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
                    ]
                ]
            ]);
    }

    /**
     * Test updating a user with validation error.
     *
     * @return void
     */
    public function test_update_user_with_validation_error(): void
    {
        $user = User::factory()->create();
        Passport::actingAs($user);
        $response = $this->put('api/users/' . $user->id, [
            'name' => '',
            'password' => 'updatedpassword',
            'role' => 'admin'
        ]);

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Test deleting a user by an admin.
     *
     * @return void
     */
    public function test_delete_user_by_admin(): void
    {
        $AdminUser = User::factory()->create(['role' => 'admin']);
        Passport::actingAs($AdminUser);
        $userToDelete = User::factory()->create();
        $response = $this->delete('api/users/' . $userToDelete->id);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);

        $this->assertNull(User::find($userToDelete->id));
    }

    /**
     * Test deleting a user by a non-admin user (should be denied).
     *
     * @return void
     */
    public function test_delete_user_by_non_admin(): void
    {
        $nonAdminUser = User::factory()->create(['role' => 'user']);
        Passport::actingAs($nonAdminUser);
        $userToDelete = User::factory()->create();
        $response = $this->delete('api/users/' . $userToDelete->id);
        $response->assertStatus(404);
        $response->assertJsonStructure([
            'status',
            'message'
        ]);
        $this->assertNotNull(User::find($userToDelete->id));
    }
}
