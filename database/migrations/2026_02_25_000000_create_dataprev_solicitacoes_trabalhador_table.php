<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dataprev_solicitacoes_trabalhador', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitacao')->unique();
            $table->string('cpf', 11)->index();
            $table->string('matricula', 100)->nullable();
            $table->unsignedTinyInteger('inscricao_empregador_codigo')->nullable();
            $table->string('inscricao_empregador_descricao', 50)->nullable();
            $table->unsignedBigInteger('numero_inscricao_empregador')->nullable();
            $table->decimal('valor_liberado', 12, 2)->nullable();
            $table->unsignedSmallInteger('nro_parcelas')->nullable();
            $table->string('data_hora_validade_solicitacao', 14)->nullable();
            $table->string('nome_trabalhador', 150)->nullable();
            $table->string('data_nascimento', 8)->nullable();
            $table->decimal('margem_disponivel', 12, 2)->nullable();
            $table->boolean('elegivel_emprestimo')->default(false);
            $table->unsignedTinyInteger('pep_codigo')->nullable();
            $table->string('pep_descricao', 100)->nullable();
            $table->string('data_admissao', 8)->nullable();
            $table->string('codigo_solicitante', 20)->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dataprev_solicitacoes_trabalhador');
    }
};
