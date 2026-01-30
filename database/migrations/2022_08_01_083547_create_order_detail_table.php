<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_detail_order_id');
            $table->bigInteger('order_detail_product_id');
            $table->decimal('order_detail_price',10,2);
            $table->integer('order_detail_quantity');
            $table->decimal('order_detail_discount', 10, 2)->default(0.0);
            $table->decimal('order_detail_price_with_dto',10,2);
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
        Schema::dropIfExists('order_detail');
    }
};
