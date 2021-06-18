<?php

namespace Tests\Feature\Product;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\AttributeGroup;
use App\Models\Order;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetProductsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function get_paginated_products()
    {
        [0 => $firstProduct, 99 => $lastProduct] = Product::factory(100)->create();

        $firstProduct->refresh();
        $lastProduct->refresh();

        $this->get(route('products.index', ['sort' => 'id']))
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    "products" => [
                        "current_page" => 1,
                        "data" => [], // assertJsonFragment() here
                        "first_page_url" => "http://localhost/api/products?page=1",
                        "from"           => 1,
                        "last_page"      => 5,
                        "last_page_url"  => "http://localhost/api/products?page=5",
                        "links" => [
                            [
                                "url"    => null,
                                "label"  => "&laquo; Previous",
                                "active" => false
                            ],
                            [
                                "url"    => "http://localhost/api/products?page=1",
                                "label"  => "1",
                                "active" => true
                            ],
                            [
                                "url"    => "http://localhost/api/products?page=2",
                                "label"  => "2",
                                "active" => false
                            ],
                        ],
                        "next_page_url" => "http://localhost/api/products?page=2",
                        "path"          => "http://localhost/api/products",
                        "per_page"      => 20,
                        "prev_page_url" => null,
                        "to"            => 20,
                        "total"         => 100
                    ]
                ]
            ])
            ->assertJsonFragment([
                "id"             => $firstProduct->id,
                "category_id"    => $firstProduct->category_id,
                "old_price"      => (string) $firstProduct->old_price,
                "price"          => (string) $firstProduct->price,
                "discount"       => $firstProduct->discount,
                "title"          => $firstProduct->title,
                "slug"           => $firstProduct->slug,
                "thumbnail_path" => $firstProduct->thumbnail_path,
                "description"    => $firstProduct->description,
                "keywords"       => $firstProduct->keywords,
            ])
            ->assertJsonMissing([
                "id"   => $lastProduct->id,
                "slug" => $lastProduct->slug,
            ]);

        $this->get(route(
            'products.index',
            ['page' => 5, 'order' => 'asc', 'sort' => 'id']
        ))
            ->assertJson([
                "data" => [
                    "products" => [
                        "current_page" => 5,
                        "data" => [
                            19 => [
                                "id"   => $lastProduct->id,
                                "slug" => $lastProduct->slug,
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function get_product()
    {
        $product = Product::factory()->create(['title' => 'Product one']);

        $this->get(route('products.show', compact('product')))
            ->assertJson([
                'status' => 'success',
                'data'   => [
                    'product' => [
                        'title' => 'Product one'
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_filter_products_by_category()
    {
        Product::factory(2)->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->get(route('products.index', ['category' => $category->id]))
            ->assertJsonCount(1, 'data.products.data')
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            [
                                'id'          => $product->id,
                                'category_id' => $category->id
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_filter_products_by_price_range()
    {
        Product::factory()->createMany([
            ['price' => 500],
            ['price' => 1000],
            ['price' => 2000],
            ['price' => 3000],
        ]);

        $this->get(route(
            'products.index',
            [
                'min_price' => 1000,
                'max_price' => 2000,
                'sort'      => 'id'
            ]
        ))
            ->assertJsonCount(2, 'data.products.data')
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            ['price' => 1000],
                            ['price' => 2000],
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function can_filter_products_by_thumbnail_presence()
    {
        Product::factory()->create();

        $thumbnailPath = File::fake()
            ->image('thumbnail.png')
            ->store('product-thumbnails', ['disk' => 'public']);

        $product = Product::factory()->create([
            'thumbnail_path' => $thumbnailPath
        ]);

        $this->get(route(
            'products.index',
            ['with_thumbnail' => true]
        ))
            ->assertJsonCount(1, 'data.products.data')
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            [
                                'id' => $product->id,
                                'thumbnail_path' => $thumbnailPath
                            ],
                        ]
                    ]
                ]
            ]);

        Storage::delete($thumbnailPath);
    }

    /** @test */
    public function can_filter_products_by_single_attribute()
    {
        Product::factory(2)->create();

        $attrbuteGroup = AttributeGroup::factory()->create(['slug' => 'author']);
        $category = Category::factory()->create();
        $category->attribute_groups()->save($attrbuteGroup);

        $product = Product::factory()->create(['category_id' => $category->id]);
        $attribute = Attribute::factory()->create([
            'value' => 'Oscar Hartmann',
            'group_id' => $attrbuteGroup->id
        ]);
        $product->attributes()->save($attribute);

        $this->get(route(
            'products.index',
            [
                'attribute' => 'author',
                'attribute_value' => 'Oscar Hartmann'
            ]
        ))
            ->assertJsonCount(1, 'data.products.data')
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            ['id' => $product->id]
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function authorized_user_can_fetch_archived_products()
    {
        $admin = User::factory()->create([
            'role_id' => Role::factory()->create(['slug' => 'admin'])
        ]);
        Product::factory(2)->create();
        $archivedProduct = Product::factory()->create(['active' => false]);

        $this->actingAs($admin)
            ->getJson(route('products.index'))
            ->assertJsonCount(3, 'data.products.data')
            ->assertJsonFragment([
                "id"   => $archivedProduct->id,
                "slug" => $archivedProduct->slug,
            ]);

        $this->actingAs(User::factory()->create())
            ->getJson(route('products.index'))
            ->assertJsonCount(2, 'data.products.data')
            ->assertJsonMissing([
                "id"   => $archivedProduct->id,
                "slug" => $archivedProduct->slug,
            ]);
    }

    /** @test */
    public function products_can_be_sorted_by_price()
    {
        Product::factory()->createMany([
            ['price' => 3000],
            ['price' => 500],
            ['price' => 2000],
            ['price' => 1000],
        ]);

        $this->getJson(route(
            'products.index',
            ['sort' => 'price']
        ))
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            ['price' => 500],
                            ['price' => 1000],
                            ['price' => 2000],
                            ['price' => 3000],
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function products_can_be_sorted_by_title()
    {
        Product::factory()->createMany([
            ['title' => 'Zelda'],
            ['title' => 'Amazon Kindle'],
            ['title' => 'Xiaomi Redmi'],
            ['title' => 'Yandex.Music'],
        ]);

        $this->getJson(route(
            'products.index',
            ['sort' => 'title', 'order' => 'desc']
        ))
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            ['title' => 'Zelda'],
                            ['title' => 'Yandex.Music'],
                            ['title' => 'Xiaomi Redmi'],
                            ['title' => 'Amazon Kindle'],
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function products_can_be_sorted_by_popularity()
    {
        // Popularity is just a better term for orders count

        $product = Product::factory()->create([
            'title' => 'Zelda',
        ]);
        $order = Order::factory()->create();
        $order->products()->attach($product, [
            'product_count' => 2,
            'product_old_price' => $product->old_price,
            'product_price' => $product->price,
            'product_discount' => $product->discount,
            'product_title' => $product->title,
        ]);


        $product = Product::factory()->create([
            'title' => 'Xiaomi Redmi',
        ]);
        $order = Order::factory()->create();
        $order->products()->attach($product, [
            'product_count' => 4,
            'product_old_price' => $product->old_price,
            'product_price' => $product->price,
            'product_discount' => $product->discount,
            'product_title' => $product->title,
        ]);

        $product = Product::factory()->create([
            'title' => 'Amazon Kindle',
        ]);
        $order = Order::factory()->create();
        $order->products()->attach($product, [
            'product_count' => 3,
            'product_old_price' => $product->old_price,
            'product_price' => $product->price,
            'product_discount' => $product->discount,
            'product_title' => $product->title,
        ]);

        $this->getJson(route(
            'products.index',
            ['sort' => 'popularity', 'order' => 'desc']
        ))
            ->assertJson([
                'data' => [
                    'products' => [
                        'data' => [
                            ['title' => 'Xiaomi Redmi'],
                            ['title' => 'Amazon Kindle'],
                            ['title' => 'Zelda'],
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function products_can_be_searched()
    {
        Product::factory(2)->create();

        $attrbuteGroup = AttributeGroup::factory()->create(['slug' => 'author']);
        $category = Category::factory()->create();
        $category->attribute_groups()->save($attrbuteGroup);

        $product = Product::factory()->create(['category_id' => $category->id]);
        $attribute = Attribute::factory()->create([
            'value' => 'Oscar Hartmann',
            'group_id' => $attrbuteGroup->id
        ]);
        $product->attributes()->save($attribute);

        $this->getJson(route('products.index', ['search' => 'Oscar']))
            ->assertJsonCount(1, 'data.products.data')
            ->assertJsonFragment([
                'id' => $product->id,
                'category_id' => $category->id,
            ]);
    }

    // /** @test */
    // public function errors_return_if_sorting_on_unknown_metric()
    // {
    //     Product::factory(2)->create();

    //     $this->getJson(route('products.index', ['sort' => 'mystery']))
    //         ->assertJson([
    //             'status' => 'success',
    //             'data' => [
    //                 'products' => [
    //                     'data' => []
    //                 ]
    //             ],
    //             'errors' => [
    //                 "Requested sort parameter 'mystery' is not supported."
    //             ]
    //         ]);
    // }
}
