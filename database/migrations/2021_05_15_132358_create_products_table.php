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
            $table->foreignId('product_category_id');
            $table->unsignedDecimal('price');
            $table->unsignedSmallInteger('discount')->default(0);
            $table->string('title');
            $table->string('slug');
            $table->string('thumbnail_url');
            $table->text('description');
            $table->text('keywords');
            $table->boolean('active')->default(true);
            $table->timestamp('active_since');
            $table->timestamp('archived_since');
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
