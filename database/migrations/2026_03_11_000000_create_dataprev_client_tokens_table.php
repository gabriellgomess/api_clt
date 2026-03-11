<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dataprev_client_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('alias', 50)->unique();
            $table->string('token', 128)->unique();
            $table->string('descricao', 200)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataprev_client_tokens');
    }
};
