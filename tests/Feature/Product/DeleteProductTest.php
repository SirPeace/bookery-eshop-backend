<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class DeleteProductTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function product_can_be_deleted()
    {
        $product = Product::factory()->create();

        $this->assertDatabaseCount('products', 1);

        $this->deleteJson(route('products.destroy', compact('product')))
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

        $this->deleteJson(route('products.destroy', compact('product')))
            ->assertJson(['status' => 'success']);

        Storage::assertMissing(
            'public/product-thumbnails/' . $thumbnail->hashName()
        );
    }
}
