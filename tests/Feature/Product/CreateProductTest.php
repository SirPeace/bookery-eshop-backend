<?php

namespace Tests\Feature\Product;

use App\Models\Product;
use Tests\TestCase;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'title' => 'Product Title',
            'category_id' => ProductCategory::factory()->create()->id,
            'price' => $this->faker->randomFloat(2, max: 10000),
            'discount' => $this->faker->numberBetween(0, 75),
            'description' => $this->faker->paragraphs(asText: true),
            'keywords' => json_encode($this->faker->words(10)),
        ];
    }

    /** @test */
    public function product_can_be_created()
    {
        $this->actingAs(User::factory()->create());

        $this->postJson(route('products.store'), $this->data)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('products', ['title' => 'Product Title']);
        $this->assertDatabaseHas(
            'products',
            ['thumbnail_path' => 'public/product-thumbnails/default.png']
        );
    }

    /** @test */
    public function product_can_be_created_with_thumbnail()
    {
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');
        $thumbnailPath = "public/product-thumbnails/" . $thumbnail->hashName();

        $data = $this->data + ['thumbnail' => $thumbnail];

        $this->actingAs(User::factory()->create());

        $this->postJson(route('products.store'), $data)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('products', [
            'title' => 'Product Title',
            'thumbnail_path' => $thumbnailPath
        ]);

        $this->assertTrue(Storage::exists($thumbnailPath));

        Storage::delete($thumbnailPath);
    }

    /** @test */
    public function product_is_not_created_if_validation_fails()
    {
        $invalidData = array_merge(
            $this->data,
            [
                'title' => 'short',
                'description' => '',
                'discount' => 100,
                'price' => -1
            ]
        );

        $this->actingAs(User::factory()->create());

        $this->postJson(route('products.store'), $invalidData)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'title' => [
                        'The title must be at least 10 characters.'
                    ],
                    'price' => [
                        'The price must be at least 0.'
                    ],
                    'discount' => [
                        'The discount must not be greater than 75.'
                    ],
                    'description' => [
                        'The description field is required.'
                    ],
                ]
            ]);

        $this->assertDatabaseMissing('products', ['title' => 'Product Title']);
    }
}
