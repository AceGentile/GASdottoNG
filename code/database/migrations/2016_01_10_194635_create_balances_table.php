<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalancesTable extends Migration
{
    public function up()
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('gas_id');
            $table->datetime('date')->useCurrent();
            $table->decimal('bank', 6, 2)->default(0);
            $table->decimal('cash', 6, 2)->default(0);
            $table->decimal('suppliers', 6, 2)->default(0);
            $table->decimal('deposits', 6, 2)->default(0);

            $table->foreign('gas_id')->references('id')->on('gas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::drop('balances');
    }
}
