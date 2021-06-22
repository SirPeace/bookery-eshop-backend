<?php

namespace Tests\Feature\Category;

use App\Models\Category;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'admin'])
        ]);
    }

    /** @test */
    public function category_can_be_deleted()
    {
        $category = Category::factory()->create();

        $this->assertDatabaseCount('categories', 1);

        $this->actingAs($this->admin)
            ->deleteJson(route('categories.destroy', compact('category')))
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('categories', 0);
    }

    /** @test */
    public function category_is_not_deleted_if_user_is_unauthorized()
    {
        $category = Category::factory()->create();
        $manager = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'manager'])
        ]);

        $this->actingAs(User::factory()->create())
            ->deleteJson(route('categories.destroy', compact('category')))
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
