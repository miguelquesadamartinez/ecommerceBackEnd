<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pharmacies', function (Blueprint $table) {
            $table->id();
            $table->string('pharmacy_sap_id')->nullable();
            $table->string('pharmacy_cip13')->nullable(); // No hay CIP en el archivo
            $table->string('pharmacy_type')->nullable();
            $table->string('pharmacy_account_status')->nullable();
            $table->string('pharmacy_status')->nullable();
            $table->string('pharmacy_name')->nullable();
            $table->string('pharmacy_name2')->nullable();
            $table->string('pharmacy_name3')->nullable();
            $table->string('pharmacy_name4')->nullable();
            $table->string('pharmacy_address_street')->nullable();
            $table->string('pharmacy_address_address1')->nullable();
            $table->string('pharmacy_address_address2')->nullable();
            $table->string('pharmacy_address_address3')->nullable();
            $table->string('pharmacy_city')->nullable();
            $table->string('pharmacy_district')->nullable();
            $table->string('pharmacy_region')->nullable();
            $table->string('pharmacy_country')->nullable();
            $table->string('pharmacy_zipcode')->nullable();
            $table->string('pharmacy_po_box')->nullable();
            $table->string('pharmacy_po_box_city')->nullable();
            $table->string('pharmacy_po_box_region')->nullable();
            $table->string('pharmacy_po_box_country')->nullable();
            $table->string('pharmacy_po_box_zipcode')->nullable();
            $table->string('pharmacy_phone')->nullable();
            $table->string('pharmacy_fax')->nullable();
            $table->string('pharmacy_email')->nullable(); // No hay email en el archivo
            $table->string('pharmacy_holder_name')->nullable();
            $table->string('pharmacy_bank_name')->nullable();
            $table->string('pharmacy_iban')->nullable();
            $table->string('pharmacy_bank_code')->nullable();
            $table->string('pharmacy_account_number')->nullable();
            $table->string('pharmacy_guichet_code')->nullable();
            $table->string('pharmacy_rib')->nullable();
            $table->string('pharmacy_siren')->nullable();
            $table->string('pharmacy_siret')->nullable();
            $table->boolean('pharmacy_new_data')->default(0);
            $table->boolean('pharmacy_new_pharmacy')->default(0);
            $table->boolean('pharmacy_sent_to_nomane')->default(0);
            $table->boolean('pharmacy_refusal_lcr')->default(0);
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
        Schema::dropIfExists('pharmacy');
    }
};
