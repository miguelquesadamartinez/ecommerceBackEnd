<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('api_call_cron_jobs');
        
        Schema::create('api_call_cron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('endpoint')->index();
            $table->string('method')->index();
            $table->integer('status_code')->index();
            $table->longText('error_message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('api_call_cron_jobs');
    }
};