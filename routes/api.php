<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataprevController;
use App\Http\Middleware\VerifyDataprevToken;

/*
|--------------------------------------------------------------------------
| API Routes - Dataprev e-Consignado
|--------------------------------------------------------------------------
|
| Rotas para integração com a API Dataprev e-Consignado.
| Todas as rotas são protegidas pelo middleware VerifyDataprevToken
| que valida o token Bearer do cliente.
|
*/

Route::prefix('dataprev')->middleware([VerifyDataprevToken::class])->group(function () {
    // Token OAuth2 (para debug/teste)
    Route::post('/token', [DataprevController::class, 'getToken']);

    // Inclusão de Proposta CTPS
    Route::post('/propostas/inclusao', [DataprevController::class, 'incluirProposta']);

    // Consulta Solicitações do Trabalhador
    Route::get('/propostas/solicitacoes-trabalhador', [DataprevController::class, 'consultarSolicitacoesTrabalhador']);

    // Inclusão de Proposta de Portabilidade CTPS
    Route::post('/propostas-portabilidade/inclusao', [DataprevController::class, 'incluirPropostaPortabilidade']);

    // Consulta Solicitações do Trabalhador Paginado (Portabilidade)
    Route::get('/propostas-portabilidade/solicitacoes-trabalhador-paginado', [DataprevController::class, 'consultarSolicitacoesTrabalhadorPortabilidadePaginado']);
});
