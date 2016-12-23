<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariantsTable extends Migration
{
    public function up()
    {
        Schema::create('variants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();

            $table->string('product_id');
            $table->string('name');
            $table->boolean('has_offset');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index('id');
        });
    }

    public function down()
    {
        Schema::drop('variants');
    }
}
