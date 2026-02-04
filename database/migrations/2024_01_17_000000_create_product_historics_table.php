<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductHistoricsTable extends Migration
{
    public function up()
    {
        Schema::create('product_historics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_historic_product_id');
            $table->string('product_historic_field_name');
            $table->text('product_historic_old_value')->nullable();
            $table->text('product_historic_new_value')->nullable();
            $table->boolean('product_historic_sent_to_nomane')->default(0);
            $table->timestamps();

            $table->foreign('product_historic_product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('product_historics');
    }
}
