<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id');
            $table->unsignedDecimal('price');
            $table->unsignedSmallInteger('discount')->default(0);
            $table->string('title');
            $table->string('slug');
            $table->string('thumbnail_path')->default(
                'public/product-images/default.png'
            );
            $table->text('description');
            $table->text('keywords');
            $table->boolean('active')->default(true);
            $table->timestamp('active_since')->nullable();
            $table->timestamp('archived_since')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
