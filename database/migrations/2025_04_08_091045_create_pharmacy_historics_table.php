<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pharmacy_historics', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pharmacy_historic_pharmacy_id');
            $table->string('pharmacy_historic_filed_name');
            $table->string('pharmacy_historic_old_value');
            $table->string('pharmacy_historic_new_value');
            $table->boolean('pharmacy_historic_sent_to_nomane')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_historics');
    }
};
