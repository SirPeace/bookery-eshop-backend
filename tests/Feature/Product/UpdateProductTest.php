<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UpdateProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_can_be_updated()
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->patchJson(
                route('products.update', compact('product')),
                [
                    'title' => 'Updated Product',
                    'price' => 5000,
                    'discount' => 20,
                    'description' => 'Very long and interesting text',
                    'keywords' => json_encode([
                        'interesting', 'keywords', 'here'
                    ]),
                ]
            )
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('products', [
            'title' => 'Updated Product',
            'price' => 5000,
            'discount' => 20,
            'description' => 'Very long and interesting text',
            'keywords' => json_encode(['interesting', 'keywords', 'here'])
        ]);
    }

    /** @test */
    public function product_thumbnail_can_be_updated()
    {
        $product = Product::factory()->create();

        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $this->actingAs(User::factory()->create());

        $this->patchJson(
                route('products.update', compact('product')),
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
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->patchJson(
                route('products.update', compact('product')),
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
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create());

        $this->patchJson(
                route('products.update', compact('product')),
                ['category_id' => 100]
            )
            ->assertJsonFragment([
                'errors' => [
                    'category_id' => ["The selected category id is invalid."]
                ]
            ]);
    }
}
