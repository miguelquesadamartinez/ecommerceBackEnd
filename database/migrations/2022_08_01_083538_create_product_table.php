<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_sap_id')->nullable();
            $table->string('product_cip13')->nullable();
            $table->bigInteger('product_category_id')->nullable();
            $table->string('product_name');
            $table->string('product_presentation')->nullable();
            $table->decimal('product_unit_price', 10, 2);
            $table->decimal('product_unit_price_pght', 10, 2)->nullable();
            $table->integer('product_box_quantity')->nullable();
            $table->integer('product_bundle_quantity')->nullable();
            $table->bigInteger('product_quote')->nullable();
            $table->bigInteger('product_allocation')->nullable();
            $table->bigInteger('product_min_order')->nullable();
            $table->bigInteger('product_max_order')->nullable();
            $table->string('product_status')->default('Disponible');
            $table->boolean('product_active')->default(true);
            $table->date('product_sell_from_date')->nullable();
            $table->time('product_sell_from_time')->nullable();
            $table->date('product_sell_to_date')->nullable();
            $table->time('product_sell_to_time')->nullable();
            $table->boolean('product_short_term')->default(false);
            $table->date('product_expiration_date')->nullable();
            $table->boolean('product_premium_offer')->default(false);
            $table->boolean('product_biomed_offer')->default(false);
            $table->string('product_sub_status')->default('Actif');
            $table->timestamps();
        });
    }
};
