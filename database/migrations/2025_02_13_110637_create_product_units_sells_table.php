<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_units_sells', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_units_sell_product_id')->index();
            $table->bigInteger('product_units_sell_units_sell')->default(0);
            $table->date('product_units_sell_date_start');
            $table->date('product_units_sell_date_end')->nullable();
            $table->time('product_units_sell_time_start');
            $table->time('product_units_sell_time_end')->nullable();
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
        Schema::dropIfExists('product_units_sells');
    }
};
