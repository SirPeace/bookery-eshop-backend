<?php

namespace Tests\Feature\User;

use App\Models\Role;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_get_itself()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('me'));

        $response->assertJson(
            [
                'status' => 'success',
                'data' => [
                    'user' => $user->toArray()
                ]
            ]
        );
    }

    /** @test */
    public function can_get_single_user_public_information()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(
            route('users.show', ['user' => $user])
        );

        $response->assertJson(
            [
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar_url' => $user->avatar_url,
                    ]
                ]
            ]
        );

        $this->assertTrue(
            collect($response->json('data.users'))->every(
                fn ($resUser) => (
                    is_null($resUser->email) &&
                    is_null($resUser->password)
                )
            )
        );
    }

    /** @test */
    public function cannot_get_single_user_hidden_information_if_not_authorized()
    {
        $user = User::factory()->create();
        $guest = User::factory()->create();

        $response = $this->actingAs($guest)->getJson(
            route('users.show', ['user' => $user, 'with_hidden' => true])
        );

        $response->assertStatus(403)
            ->assertJson(
                ['message' => 'This action is unauthorized.']
            );
    }

    /** @test */
    public function can_get_single_user_hidden_information()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(
            route('users.show', ['user' => $user, 'with_hidden' => true])
        );

        $response->assertJson(
            [
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'avatar_url' => $user->avatar_url,
                        'email' => $user->email,
                        'password' => $user->password
                    ]
                ]
            ]
        );
    }

    /** @test */
    public function can_get_all_users_public_information()
    {
        $users = User::factory(2)->create();

        $response = $this->actingAs($users[0])->getJson(route('users.index'));

        $response->assertJson(
            [
                'status' => 'success',
                'data' => [
                    'users' => []
                ]
            ]
        );

        // Each user in response has valid fields: id, name, avatar_url
        $this->assertTrue(
            collect($response->json('data.users'))->every(
                function ($resUser, $index) use ($users) {
                    $user = $users[$index];

                    return (
                        $resUser['id'] == $user->id &&
                        $resUser['avatar_url'] == $user->avatar_url &&
                        $resUser['name'] == $user->name
                    );
                }
            )
        );

        // Emails are not passed in the response
        $this->assertTrue(
            collect($response->json('data.users'))->every(
                fn ($resUser) => (
                    !key_exists('email', $resUser) &&
                    !key_exists('password', $resUser)
                )
            )
        );
    }

    /** @test */
    public function cannot_get_all_users_hidden_information_if_not_authorized()
    {
        $users = User::factory(2)->create();

        $response = $this->actingAs($users[0])->getJson(
            route('users.index', ['with_hidden' => true])
        );

        $response->assertStatus(403)
            ->assertJson(
                ['message' => 'This action is unauthorized.']
            );
    }

    /** @test */
    public function can_get_all_users_hidden_information()
    {
        $users = User::factory(2)->create();
        $manager = User::factory()->create(
            ['role_id' => Role::factory()->create(['slug' => 'manager'])->id]
        );

        $response = $this->actingAs($manager)->getJson(
            route('users.index', ['with_hidden' => true])
        );

        $response->assertJson(
            [
                'status' => 'success',
                'data' => [
                    'users' => []
                ]
            ]
        );

        $this->assertTrue(
            collect($response->json('data.users'))->every(
                function ($resUser, $index) use ($users, $manager) {
                    $users = [...$users, $manager];

                    $user = $users[$index];

                    return (
                        $resUser['id'] == $user->id &&
                        $resUser['avatar_url'] == $user->avatar_url &&
                        $resUser['name'] == $user->name &&
                        $resUser['email'] == $user->email &&
                        $resUser['password'] == $user->password
                    );
                }
            )
        );
    }
}
