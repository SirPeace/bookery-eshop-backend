<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()
            ->for(Role::factory())
            ->has(Order::factory()->count(3))
            ->create();
    }

    /** @test */
    public function user_belongs_to_role()
    {
        $this->assertInstanceOf(Role::class, $this->user->role);
    }

    /** @test */
    public function user_has_many_orders()
    {
        $this->assertContainsOnlyInstancesOf(Order::class, $this->user->orders);
        $this->assertCount(3, $this->user->orders);
    }
}
