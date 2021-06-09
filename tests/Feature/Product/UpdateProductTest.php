<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateProductTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['name' => 'admin'])
        ]);

        $this->product = Product::factory()->create();
    }

    /** @test */
    public function product_can_be_updated()
    {
        $updatedFields = [
            'title' => 'Updated Product',
            'price' => 5000,
            'discount' => 20,
            'description' => 'Very long and interesting text',
            'keywords' => json_encode([
                'interesting', 'keywords', 'here'
            ]),
        ];

        $this->actingAs($this->admin)
            ->patchJson(
                route('products.update', ['product' => $this->product]),
                $updatedFields
            )
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'product' => $updatedFields
                ]
            ]);

        $this->assertDatabaseHas('products', $updatedFields);
    }

    /** @test */
    public function product_thumbnail_can_be_updated()
    {
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $this->actingAs($this->admin)
            ->patchJson(
                route('products.update', ['product' => $this->product]),
                ['thumbnail' => $thumbnail]
            )
            ->assertJson(['status' => 'success']);

        $thumbPath = "public/product-thumbnails/{$thumbnail->hashName()}";

        $this->assertDatabaseHas('products', [
            'thumbnail_path' => $thumbPath,
        ]);

        $this->assertTrue(Storage::exists($thumbPath));

        Storage::delete($thumbPath);
    }

    /** @test */
    public function product_is_not_updated_if_validation_fails()
    {
        $this->actingAs($this->admin)
            ->patchJson(
                route('products.update', ['product' => $this->product]),
                ['category_id' => 100]
            )
            ->assertStatus(422);

        $this->assertDatabaseMissing('products', [
            'category_id' => 100,
        ]);
    }

    /** @test */
    public function returns_errors_if_validation_fails()
    {
        $this->actingAs($this->admin)
            ->patchJson(
                route('products.update', ['product' => $this->product]),
                ['category_id' => 100]
            )
            ->assertJsonFragment([
                'errors' => [
                    'category_id' => ["The selected category id is invalid."]
                ]
            ]);
    }

    /** @test */
    public function product_is_not_updated_if_user_is_unauthorized()
    {
        $this->actingAs(User::factory()->create())
            ->patchJson(
                route('products.update', ['product' => $this->product]),
                ['title' => 'Updated Product']
            )
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseMissing(
            'products',
            ['title' => 'Updated Product']
        );
    }
}
