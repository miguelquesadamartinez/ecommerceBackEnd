<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders_cagedim', function (Blueprint $table) {
            $table->id();
            $table->string('sales_org');
            $table->string('sold_to');
            $table->string('ship_to');
            $table->string('customer_po');
            $table->string('po_type');
            $table->string('order_block_code')->nullable();
            $table->string('shipment_method');
            $table->string('delivery_priority');
            $table->date('po_date');
            $table->date('requested_delivery_date');
            $table->boolean('order_sent_to_nomane')->default(0);
            $table->date('order_sent_to_nomane_date')->nullable();
            $table->timestamps();
        });

        Schema::create('orders_cagedim_header_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders_cagedim')->onDelete('cascade');
            $table->string('text_type');
            $table->text('free_text');
            $table->timestamps();
        });

        Schema::create('orders_cagedim_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders_cagedim')->onDelete('cascade');
            $table->string('product_no');
            $table->string('product_qualifier_code');
            $table->integer('qty');
            $table->string('item_category');
            $table->timestamps();

            // Campos para el descuento
            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 5, 2)->nullable();

            // Índices
            $table->index('product_no');
            $table->index('order_id');
        });

        Schema::create('orders_cagedim_line_texts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_line_id')->constrained('orders_cagedim_lines')->onDelete('cascade');
            $table->string('text_type');
            $table->text('free_text');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders_cagedim_line_texts');
        Schema::dropIfExists('orders_cagedim_lines');
        Schema::dropIfExists('orders_cagedim_header_texts');
        Schema::dropIfExists('orders_cagedim');
    }
};

