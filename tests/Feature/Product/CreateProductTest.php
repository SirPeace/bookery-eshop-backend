<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Role;
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

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['name' => 'admin'])
        ]);

        $oldPrice = $this->faker->randomFloat(2, min: 10, max: 10000);
        $price = $this->faker->randomFloat(
            nbMaxDecimals: 2,
            min: $oldPrice * 0.35,
            max: $oldPrice * 0.9
        );
        $discount = intval((1 - $price / $oldPrice) * 100);

        $this->data = [
            'title' => 'Product Title',
            'category_id' => Category::factory()->create()->id,
            'old_price' => $oldPrice,
            'price' => $price,
            'discount' => $discount,
            'description' => $this->faker->paragraphs(asText: true),
            'keywords' => json_encode($this->faker->words(10)),
        ];
    }

    /** @test */
    public function product_can_be_created()
    {
        $this->actingAs($this->admin)
            ->postJson(route('products.store'), $this->data)
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'product' => $this->data
                ]
            ]);

        $this->assertDatabaseHas('products', $this->data);
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

        $this->actingAs($this->admin)
            ->postJson(route('products.store'), $data)
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('products', [
            'title' => 'Product Title',
            'thumbnail_path' => $thumbnailPath
        ]);

        Storage::assertExists($thumbnailPath);

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

        $this->actingAs($this->admin)
            ->postJson(route('products.store'), $invalidData)
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

        $this->assertDatabaseMissing('products', ['title' => 'short']);
    }

    /** @test */
    public function product_is_not_created_if_user_is_unauthorized()
    {
        $this->actingAs(User::factory()->create())
            ->postJson(route('products.store'), $this->data)
            ->assertStatus(422)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseMissing('products', ['title' => 'Product Title']);
    }
}
