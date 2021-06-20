<?php

namespace Tests\Feature\Category;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateCategoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'admin'])
        ]);

        $this->data = [
            'title' => 'Very impressive title',
            'slug' => 'very-impressive-title',
            'keywords' => json_encode(['very', 'important', 'keywords']),
            'description' => $this->faker->paragraph()
        ];
    }

    /** @test */
    public function can_create_category()
    {
        $this->actingAs($this->admin)
            ->postJson(route('categories.store'), $this->data)
            ->assertJson([
                "status" => "success",
                "data" => [
                    "category" => $this->data
                ]
            ]);

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseHas('categories', $this->data);
    }

    /** @test */
    public function can_create_child_category()
    {
        $category = Category::factory()->create();
        $this->data = $this->data + ['parent_id' => $category->id];

        $this->actingAs($this->admin)
            ->postJson(route('categories.store'), $this->data)
            ->assertJson([
                "status" => "success",
                "data" => [
                    "category" => $this->data
                ]
            ]);

        $this->assertDatabaseCount('categories', 2);
        $this->assertDatabaseHas('categories', $this->data);
        $this->assertEquals(
            $category->id,
            Category::where('slug', $this->data['slug'])->first()->parent->id
        );
    }

    /** @test */
    public function unauthorized_user_cannot_create_category()
    {
        $this->postJson(route('categories.store'), $this->data)
            ->assertStatus(403)
            ->assertJson([
                "message" => "This action is unauthorized."
            ]);

        $this->assertDatabaseMissing('categories', $this->data);
    }

    /** @test */
    public function category_cannot_be_created_with_invalid_data()
    {
        $invalidData = [
            'title' => 1,
            'slug' => '$$$$$$',
            'keywords' => 'very long keyword list',
            'description' => '...',
        ];

        $this->actingAs($this->admin)
            ->postJson(route('categories.store'), $invalidData)
            ->assertStatus(422)
            ->assertJson([
                "message" => "The given data was invalid.",
                "errors" => [
                    "title" => [
                        "The title must be a string.",
                        "The title must be at least 5 characters."
                    ],
                    "keywords" => [
                        "The keywords format is invalid."
                    ],
                    "description" => [
                        "The description must be at least 10 characters."
                    ]

                ]
            ]);

        $this->assertDatabaseMissing('products', $invalidData);
    }
}
