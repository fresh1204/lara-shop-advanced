<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');             //类目名称
            $table->unsignedInteger('parent_id')->nullable(); //父类目ID
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade'); 
            $table->boolean('is_directory'); //是否拥有子类目
            $table->unsignedInteger('level'); //当前类目层级
            $table->string('path'); //该类目所有父类目 id
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
        Schema::dropIfExists('categories');
    }
}
