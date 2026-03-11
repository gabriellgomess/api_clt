<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitacaoTrabalhador extends Model
{
    protected $table = 'dataprev_solicitacoes_trabalhador';

    protected $fillable = [
        'id_solicitacao',
        'cpf',
        'matricula',
        'inscricao_empregador_codigo',
        'inscricao_empregador_descricao',
        'numero_inscricao_empregador',
        'valor_liberado',
        'nro_parcelas',
        'data_hora_validade_solicitacao',
        'nome_trabalhador',
        'data_nascimento',
        'margem_disponivel',
        'elegivel_emprestimo',
        'pep_codigo',
        'pep_descricao',
        'data_admissao',
        'codigo_solicitante',
        'client_token',
    ];
}
