<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_threshold_prices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_threshold_price_product_id')->index();
            $table->integer('product_threshold_price_level');
            $table->integer('product_threshold_price_threshold_from');
            $table->integer('product_threshold_price_threshold_to')->nullable();
            $table->decimal('product_threshold_price_price',10,2)->default(0)->nullable();
            $table->integer('product_threshold_price_threshold_from_premium')->default(1);
            $table->integer('product_threshold_price_threshold_to_premium')->nullable();
            $table->decimal('product_threshold_price_discount',10,2)->default(0)->nullable();
            $table->decimal('product_threshold_price_price_premium',10,2)->default(0)->nullable();
            $table->decimal('product_threshold_price_discount_premium',10,2)->default(0)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_threshold_prices');
    }
};
