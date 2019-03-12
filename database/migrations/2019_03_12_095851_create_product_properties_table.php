<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

//商品属性
class CreateProductPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_properties', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('product_id');  // 商品 ID
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('name');  // 属性名称
            $table->string('value'); // 属性值
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_properties');
    }
}
