<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('file_statuses', function (Blueprint $table) {
            $table->id();

            $table->string('file_status_filename');
            $table->string('file_status_status'); // Not Processed - Starting process - Processed - Error
            $table->string('file_status_source');
            $table->string('file_status_process'); // In - Out
            $table->string('file_status_type'); // Each file type
            $table->boolean('file_status_error')->nullable();
            $table->string('file_status_error_message')->nullable();
            $table->string('file_status_error_code')->nullable();
            $table->string('file_status_error_line')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_statuses');
    }
};
