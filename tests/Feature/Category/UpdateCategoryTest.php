<?php

use App\Models\Category;
use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['name' => 'admin'])
        ]);

        $this->category = Category::factory()->create();
    }

    /** @test */
    public function can_update_category()
    {
        $parentCategory = Category::factory()->create();
        $updatedFields = [
            'title' => 'Updated Product',
            'slug' => 'updated-product',
            'parent_id' => $parentCategory->id,
            'description' => 'Very long and interesting text',
            'keywords' => json_encode(['interesting', 'keywords', 'here']),
        ];

        $this->actingAs($this->admin)
            ->patchJson(
                route('categories.update', ['category' => $this->category]),
                $updatedFields
            )
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'category' => $updatedFields
                ]
            ]);

        $this->assertDatabaseHas('categories', $parentCategory->toArray());
        $this->assertDatabaseHas('categories', $updatedFields);
    }

    /** @test */
    public function category_is_not_updated_if_validation_fails()
    {
        $this->actingAs($this->admin)
            ->patchJson(
                route('categories.update', ['category' => $this->category]),
                ['parent_id' => -1]
            )
            ->assertStatus(422);

        $this->assertDatabaseMissing('categories', ['parent_id' => -1]);
    }

    /** @test */
    public function returns_errors_if_validation_fails()
    {
        $this->actingAs($this->admin)
            ->patchJson(
                route('categories.update', ['category' => $this->category]),
                ['parent_id' => -1]
            )
            ->assertJsonFragment([
                'errors' => [
                    'parent_id' => ["The selected parent id is invalid."]
                ]
            ]);
    }

    /** @test */
    public function category_is_not_updated_if_user_is_unauthorized()
    {
        $this->actingAs(User::factory()->create())
            ->patchJson(
                route('categories.update', ['category' => $this->category]),
                ['title' => 'Updated category']
            )
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseMissing(
            'categories',
            ['title' => 'Updated category']
        );
    }
}
