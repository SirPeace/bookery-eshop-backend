<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->for(Role::factory())->create();
    }

    /** @test */
    public function user_belongs_to_role()
    {
        $this->assertInstanceOf(Role::class, $this->user->role);
    }
}
