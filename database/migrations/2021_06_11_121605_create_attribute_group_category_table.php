<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeGroupCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_group_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_group_id')->constrained();
            $table->foreignId('category_id')->constrained();
            $table->timestamps();

            $table->unique(['attribute_group_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('attribute_group_category');
    }
}
