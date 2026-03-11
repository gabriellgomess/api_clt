<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dataprev_solicitacoes_trabalhador', function (Blueprint $table) {
            $table->string('client_token', 50)->nullable()->after('codigo_solicitante')->index();
        });

        // Registros existentes são atribuídos ao sistema-a
        DB::table('dataprev_solicitacoes_trabalhador')
            ->whereNull('client_token')
            ->update(['client_token' => 'sistema-a']);
    }

    public function down(): void
    {
        Schema::table('dataprev_solicitacoes_trabalhador', function (Blueprint $table) {
            $table->dropColumn('client_token');
        });
    }
};
