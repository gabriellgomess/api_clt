<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dataprev_request_logs', function (Blueprint $table) {
            $table->id();
            $table->string('client_token', 50)->index();
            $table->string('endpoint', 80)->index();
            $table->unsignedSmallInteger('http_status');
            $table->unsignedInteger('results_count')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataprev_request_logs');
    }
};
