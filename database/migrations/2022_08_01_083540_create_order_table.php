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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_reference')->nullable();
            $table->bigInteger('order_user_id')->default(0);
            $table->bigInteger('order_pharmacy_id');
            $table->decimal('order_amount',10,2);
            $table->date('order_desired_delivery_date')->nullable();
            $table->string('order_status')->default('Draft');
            $table->boolean('order_blocked')->default(0);
            $table->string('order_block_reason')->nullable();
            $table->boolean('order_sent_to_nomane')->default(0);
            $table->date('order_sent_to_nomane_date')->nullable();
            $table->string('order_source'); // Call - APM (External network)
            $table->date('order_retain_from_date')->nullable();
            $table->time('order_retain_from_time')->nullable();
            $table->date('order_retain_to_date')->nullable();
            $table->time('order_retain_to_time')->nullable();
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
        Schema::dropIfExists('orders');
    }
};
