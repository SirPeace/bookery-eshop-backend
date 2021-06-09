<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeleteProductTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role_id' => Role::factory()->create(['name' => 'admin'])
        ]);
    }

    /** @test */
    public function product_can_be_deleted()
    {
        $product = Product::factory()->create();

        $this->assertDatabaseCount('products', 1);

        $this->actingAs($this->admin)
            ->deleteJson(route('products.destroy', compact('product')))
            ->assertJson(['status' => 'success']);

        $this->assertDatabaseCount('products', 0);
    }

    /** @test */
    public function thumbnail_is_deleted_with_product()
    {
        $thumbnail = UploadedFile::fake()->image('thumbnail.png');

        $product = Product::factory()->create([
            'thumbnail_path' => $thumbnail->store('public/product-thumbnails')
        ]);

        Storage::assertExists(
            'public/product-thumbnails/' . $thumbnail->hashName()
        );

        $this->actingAs($this->admin)
            ->deleteJson(route('products.destroy', compact('product')))
            ->assertJson(['status' => 'success']);

        Storage::assertMissing(
            'public/product-thumbnails/' . $thumbnail->hashName()
        );
    }

    /** @test */
    public function product_is_not_deleted_if_user_is_unauthorized()
    {
        $product = Product::factory()->create();

        $this->actingAs(User::factory()->create())
            ->deleteJson(
                route('products.destroy', compact('product')),
                ['title' => 'Updated Product']
            )
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);

        $this->assertDatabaseHas('products', ['title' => $product->title]);
    }
}
