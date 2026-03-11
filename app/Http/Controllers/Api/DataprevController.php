<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitacaoTrabalhador;
use App\Services\DataprevService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Endpoints de autenticação e token OAuth2"
 * )
 * @OA\Tag(
 *     name="Propostas CTPS",
 *     description="Endpoints para inclusão e consulta de propostas CTPS"
 * )
 * @OA\Tag(
 *     name="Portabilidade CTPS",
 *     description="Endpoints para inclusão e consulta de propostas de portabilidade CTPS"
 * )
 */
class DataprevController extends Controller
{
    protected $dataprevService;

    public function __construct(DataprevService $dataprevService)
    {
        $this->dataprevService = $dataprevService;
    }

    /**
     * Obtém token de acesso OAuth2
     *
     * @OA\Post(
     *     path="/dataprev/token",
     *     summary="Obtém token de acesso OAuth2",
     *     description="Obtém um novo token de acesso OAuth2 da Dataprev. O token é cacheado automaticamente por 55 minutos.",
     *     tags={"Autenticação"},
     *     security={{ "dataprevAuth": {} }},
     *     @OA\Parameter(
     *         name="refresh",
     *         in="query",
     *         description="Se true, força a renovação do token (ignora cache)",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Token obtido com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="eyJ4NXQiOiJxLUZpUURHNEFhWjh2NjN...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Token de autenticação do cliente inválido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token de autenticação inválido ou não fornecido."),
     *             @OA\Property(property="error", type="string", example="Unauthenticated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao obter token OAuth2",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="error", type="string", example="Não foi possível obter o token de acesso")
     *         )
     *     )
     * )
     */
    public function getToken(Request $request)
    {
        $refresh = $request->boolean('refresh', false);

        $token = $refresh
            ? $this->dataprevService->refreshToken()
            : $this->dataprevService->getAccessToken();

        if ($token) {
            return response()->json([
                'success' => true,
                'token' => $token,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Não foi possível obter o token de acesso',
        ], 500);
    }

    /**
     * Inclusão de Proposta CTPS
     *
     * @OA\Post(
     *     path="/dataprev/propostas/inclusao",
     *     summary="Inclusão de Proposta CTPS",
     *     description="Realiza a inclusão de uma proposta de consignado no sistema Dataprev e-Consignado.",
     *     tags={"Propostas CTPS"},
     *     security={{ "dataprevAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroProposta","idSolicitacaoProposta","dataHoraValidadeProposta","numeroParcelas","valorParcela","valorLiberado","valorEmprestimo","valorIOF","valorTaxaAnual","valorCETAnual","valorTaxaMensal","valorCETMensal","contatos"},
     *             @OA\Property(property="codigoSolicitante", type="string", example="0526", description="Código do solicitante (opcional, usa valor do .env se não informado)"),
     *             @OA\Property(property="numeroProposta", type="string", example="199912345678", description="Número da proposta (12 dígitos)"),
     *             @OA\Property(property="idSolicitacaoProposta", type="integer", example=30774347, description="ID da solicitação da proposta"),
     *             @OA\Property(property="dataHoraValidadeProposta", type="string", example="31012030100000", description="Data/hora de validade (DDMMYYYYHHmmss)"),
     *             @OA\Property(property="numeroParcelas", type="integer", example=24, description="Número de parcelas"),
     *             @OA\Property(property="valorParcela", type="number", format="float", example=200.00, description="Valor de cada parcela"),
     *             @OA\Property(property="valorLiberado", type="number", format="float", example=1000.00, description="Valor liberado ao trabalhador"),
     *             @OA\Property(property="valorEmprestimo", type="number", format="float", example=1500.00, description="Valor total do empréstimo"),
     *             @OA\Property(property="valorIOF", type="number", format="float", example=0.10, description="Valor do IOF"),
     *             @OA\Property(property="valorTaxaAnual", type="number", format="float", example=1.20, description="Taxa de juros anual (%)"),
     *             @OA\Property(property="valorCETAnual", type="number", format="float", example=1.00, description="Custo Efetivo Total anual (%)"),
     *             @OA\Property(property="valorTaxaMensal", type="number", format="float", example=0.50, description="Taxa de juros mensal (%)"),
     *             @OA\Property(property="valorCETMensal", type="number", format="float", example=0.50, description="Custo Efetivo Total mensal (%)"),
     *             @OA\Property(
     *                 property="contatos",
     *                 type="array",
     *                 description="Lista de contatos do trabalhador",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"tipo","contato"},
     *                     @OA\Property(property="tipo", type="integer", example=3, description="Tipo de contato (3=email)"),
     *                     @OA\Property(property="contato", type="string", example="meajuda@monbank.net", description="Valor do contato")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposta incluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=422, description="Erro de validação dos dados"),
     *     @OA\Response(response=500, description="Erro interno do servidor")
     * )
     */
    public function incluirProposta(Request $request)
    {
        $data = $request->validate([
            'codigoSolicitante' => 'nullable|string',
            'numeroProposta' => 'required|string|size:12',
            'idSolicitacaoProposta' => 'required|integer',
            'dataHoraValidadeProposta' => 'required|string|size:14',
            'numeroParcelas' => 'required|integer',
            'valorParcela' => 'required|numeric',
            'valorLiberado' => 'required|numeric',
            'valorEmprestimo' => 'required|numeric',
            'valorIOF' => 'required|numeric',
            'valorTaxaAnual' => 'required|numeric',
            'valorCETAnual' => 'required|numeric',
            'valorTaxaMensal' => 'required|numeric',
            'valorCETMensal' => 'required|numeric',
            'contatos' => 'required|array|min:1',
            'contatos.*.tipo' => 'required|integer',
            'contatos.*.contato' => 'required|string',
        ]);

        $response = $this->dataprevService->incluirProposta($data);

        return response()->json($response, $response['status']);
    }

    /**
     * Inclusão de Proposta de Portabilidade CTPS
     *
     * @OA\Post(
     *     path="/dataprev/propostas-portabilidade/inclusao",
     *     summary="Inclusão de Proposta de Portabilidade CTPS",
     *     description="Realiza a inclusão de uma proposta de portabilidade de consignado no sistema Dataprev.",
     *     tags={"Portabilidade CTPS"},
     *     security={{ "dataprevAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"numeroProposta","idSolicitacaoProposta","dataHoraValidadeProposta","numeroParcelas","valorParcela","valorEmprestimo","valorTaxaAnual","valorCETAnual","valorTaxaMensal","valorCETMensal","contatos"},
     *             @OA\Property(property="codigoSolicitante", type="string", example="0526", description="Código do solicitante (opcional)"),
     *             @OA\Property(property="numeroProposta", type="string", example="199912345678", description="Número da proposta (12 dígitos)"),
     *             @OA\Property(property="idSolicitacaoProposta", type="integer", example=1234, description="ID da solicitação da proposta"),
     *             @OA\Property(property="dataHoraValidadeProposta", type="string", example="28102025103500", description="Data/hora de validade (DDMMYYYYHHmmss)"),
     *             @OA\Property(property="numeroParcelas", type="integer", example=24, description="Número de parcelas"),
     *             @OA\Property(property="valorParcela", type="number", format="float", example=200.00, description="Valor de cada parcela"),
     *             @OA\Property(property="valorEmprestimo", type="number", format="float", example=1500.00, description="Valor total do empréstimo"),
     *             @OA\Property(property="valorTaxaAnual", type="number", format="float", example=1.20, description="Taxa de juros anual (%)"),
     *             @OA\Property(property="valorCETAnual", type="number", format="float", example=1.00, description="CET anual (%)"),
     *             @OA\Property(property="valorTaxaMensal", type="number", format="float", example=0.50, description="Taxa de juros mensal (%)"),
     *             @OA\Property(property="valorCETMensal", type="number", format="float", example=0.50, description="CET mensal (%)"),
     *             @OA\Property(
     *                 property="contatos",
     *                 type="array",
     *                 description="Lista de contatos",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"tipo","contato"},
     *                     @OA\Property(property="tipo", type="integer", example=3, description="Tipo de contato (3=email)"),
     *                     @OA\Property(property="contato", type="string", example="email@example.com", description="Valor do contato")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Proposta de portabilidade incluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=422, description="Erro de validação dos dados"),
     *     @OA\Response(response=500, description="Erro interno do servidor")
     * )
     */
    public function incluirPropostaPortabilidade(Request $request)
    {
        $data = $request->validate([
            'codigoSolicitante' => 'nullable|string',
            'numeroProposta' => 'required|string|size:12',
            'idSolicitacaoProposta' => 'required|integer',
            'dataHoraValidadeProposta' => 'required|string|size:14',
            'numeroParcelas' => 'required|integer',
            'valorParcela' => 'required|numeric',
            'valorEmprestimo' => 'required|numeric',
            'valorTaxaAnual' => 'required|numeric',
            'valorCETAnual' => 'required|numeric',
            'valorTaxaMensal' => 'required|numeric',
            'valorCETMensal' => 'required|numeric',
            'contatos' => 'required|array|min:1',
            'contatos.*.tipo' => 'required|integer',
            'contatos.*.contato' => 'required|string',
        ]);

        $response = $this->dataprevService->incluirPropostaPortabilidade($data);

        return response()->json($response, $response['status']);
    }

    /**
     * Consulta Solicitações do Trabalhador
     *
     * @OA\Get(
     *     path="/dataprev/propostas/solicitacoes-trabalhador",
     *     summary="Consulta Solicitações do Trabalhador",
     *     description="Consulta as solicitações de propostas do trabalhador no sistema Dataprev.",
     *     tags={"Propostas CTPS"},
     *     security={{ "dataprevAuth": {} }},
     *     @OA\Parameter(
     *         name="codigoSolicitante",
     *         in="query",
     *         description="Código do solicitante (opcional, usa valor do .env se não informado)",
     *         required=false,
     *         @OA\Schema(type="string", example="0526")
     *     ),
     *     @OA\Parameter(
     *         name="idSolicitacaoProposta",
     *         in="query",
     *         description="ID específico de uma solicitação de proposta",
     *         required=false,
     *         @OA\Schema(type="integer", example=30774347)
     *     ),
     *     @OA\Parameter(
     *         name="nroPagina",
     *         in="query",
     *         description="Número da página para paginação",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="dataHoraInicio",
     *         in="query",
     *         description="Data/hora de início para filtro (formato DDMMYYYYHHmmss)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="dataHoraFim",
     *         in="query",
     *         description="Data/hora de fim para filtro (formato DDMMYYYYHHmmss)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consulta realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Lista de solicitações do trabalhador",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="idSolicitacao", type="integer", example=10000001, description="ID da solicitação"),
     *                     @OA\Property(property="cpf", type="integer", example=12345678900, description="CPF do trabalhador (sem formatação)"),
     *                     @OA\Property(property="matricula", type="string", example="MAT000001", description="Matrícula do trabalhador"),
     *                     @OA\Property(
     *                         property="inscricaoEmpregador",
     *                         type="object",
     *                         description="Tipo de inscrição do empregador",
     *                         @OA\Property(property="codigo", type="integer", example=1, description="1=CNPJ, 2=CPF"),
     *                         @OA\Property(property="descricao", type="string", example="CNPJ")
     *                     ),
     *                     @OA\Property(property="numeroInscricaoEmpregador", type="integer", example=11222333000100, description="CNPJ ou CPF do empregador"),
     *                     @OA\Property(property="valorLiberado", type="number", format="float", example=5000.00, description="Valor liberado para empréstimo"),
     *                     @OA\Property(property="nroParcelas", type="integer", example=12, description="Número de parcelas"),
     *                     @OA\Property(property="dataHoraValidadeSolicitacao", type="string", example="10032026120000", description="Validade da solicitação (DDMMYYYYHHmmss)"),
     *                     @OA\Property(property="nomeTrabalhador", type="string", example="João da Silva", description="Nome completo do trabalhador"),
     *                     @OA\Property(property="dataNascimento", type="string", example="15061990", description="Data de nascimento (DDMMYYYY)"),
     *                     @OA\Property(property="margemDisponivel", type="number", format="float", example=3500.00, description="Margem consignável disponível"),
     *                     @OA\Property(property="elegivelEmprestimo", type="boolean", example=true, description="Se o trabalhador é elegível"),
     *                     @OA\Property(property="dataAdmissao", type="string", example="01032020", description="Data de admissão (DDMMYYYY)")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=500, description="Erro interno do servidor")
     * )
     */
    public function consultarSolicitacoesTrabalhador(Request $request)
    {
        $params = $request->only([
            'codigoSolicitante',
            'idSolicitacaoProposta',
            'nroPagina',
            'dataHoraInicio',
            'dataHoraFim',
        ]);

        // Remove parâmetros vazios/nulos
        $params = array_filter($params, fn($value) => !is_null($value) && $value !== '');

        $response = $this->dataprevService->consultarSolicitacoesTrabalhador($params);

        if ($response['success'] && !empty($response['data'])) {
            $codigoSolicitante = $params['codigoSolicitante']
                ?? config('dataprev.codigo_solicitante');

            $clientToken = $request->attributes->get('dataprev_client', 'sistema-a');

            $now = now();

            $records = array_map(function ($item) use ($codigoSolicitante, $clientToken, $now) {
                return [
                    'id_solicitacao'                  => $item['idSolicitacao'],
                    'cpf'                             => str_pad((string) $item['cpf'], 11, '0', STR_PAD_LEFT),
                    'matricula'                       => $item['matricula'] ?? null,
                    'inscricao_empregador_codigo'     => $item['inscricaoEmpregador']['codigo'] ?? null,
                    'inscricao_empregador_descricao'  => $item['inscricaoEmpregador']['descricao'] ?? null,
                    'numero_inscricao_empregador'     => $item['numeroInscricaoEmpregador'] ?? null,
                    'valor_liberado'                  => $item['valorLiberado'] ?? null,
                    'nro_parcelas'                    => $item['nroParcelas'] ?? null,
                    'data_hora_validade_solicitacao'  => $item['dataHoraValidadeSolicitacao'] ?? null,
                    'nome_trabalhador'                => $item['nomeTrabalhador'] ?? null,
                    'data_nascimento'                 => $item['dataNascimento'] ?? null,
                    'margem_disponivel'               => $item['margemDisponivel'] ?? null,
                    'elegivel_emprestimo'             => $item['elegivelEmprestimo'] ?? false,
                    'pep_codigo'                      => $item['pessoaExpostaPoliticamente']['codigo'] ?? null,
                    'pep_descricao'                   => $item['pessoaExpostaPoliticamente']['descricao'] ?? null,
                    'data_admissao'                   => $item['dataAdmissao'] ?? null,
                    'codigo_solicitante'              => $codigoSolicitante,
                    'client_token'                    => $clientToken,
                    'created_at'                      => $now,
                    'updated_at'                      => $now,
                ];
            }, $response['data']);

            $updateColumns = [
                'cpf', 'matricula', 'inscricao_empregador_codigo', 'inscricao_empregador_descricao',
                'numero_inscricao_empregador', 'valor_liberado', 'nro_parcelas',
                'data_hora_validade_solicitacao', 'nome_trabalhador', 'data_nascimento',
                'margem_disponivel', 'elegivel_emprestimo', 'pep_codigo', 'pep_descricao',
                'data_admissao', 'codigo_solicitante', 'client_token', 'updated_at',
            ];

            try {
                foreach (array_chunk($records, 500) as $chunk) {
                    SolicitacaoTrabalhador::upsert($chunk, ['id_solicitacao'], $updateColumns);
                }
            } catch (\Exception $e) {
                Log::error('Dataprev UPSERT Error', ['error' => $e->getMessage()]);
            }
        }

        return response()->json($response, $response['status']);
    }

    /**
     * Consulta Solicitações do Trabalhador Paginado (Portabilidade)
     *
     * @OA\Get(
     *     path="/dataprev/propostas-portabilidade/solicitacoes-trabalhador-paginado",
     *     summary="Consulta Solicitações do Trabalhador Paginado (Portabilidade)",
     *     description="Consulta as solicitações de propostas de portabilidade do trabalhador com paginação.",
     *     tags={"Portabilidade CTPS"},
     *     security={{ "dataprevAuth": {} }},
     *     @OA\Parameter(
     *         name="codigoSolicitante",
     *         in="query",
     *         description="Código do solicitante (opcional, usa valor do .env se não informado)",
     *         required=false,
     *         @OA\Schema(type="string", example="0526")
     *     ),
     *     @OA\Parameter(
     *         name="idSolicitacaoProposta",
     *         in="query",
     *         description="ID específico de uma solicitação de proposta",
     *         required=false,
     *         @OA\Schema(type="integer", example=30774347)
     *     ),
     *     @OA\Parameter(
     *         name="dataHoraInicio",
     *         in="query",
     *         description="Data/hora de início para filtro (formato DDMMYYYYHHmmss)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="dataHoraFim",
     *         in="query",
     *         description="Data/hora de fim para filtro (formato DDMMYYYYHHmmss)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Consulta realizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object", description="Dados paginados das solicitações de portabilidade")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=500, description="Erro interno do servidor")
     * )
     */
    public function consultarSolicitacoesTrabalhadorPortabilidadePaginado(Request $request)
    {
        $params = $request->only([
            'codigoSolicitante',
            'idSolicitacaoProposta',
            'dataHoraInicio',
            'dataHoraFim',
        ]);

        // Remove parâmetros vazios/nulos
        $params = array_filter($params, fn($value) => !is_null($value) && $value !== '');

        $response = $this->dataprevService->consultarSolicitacoesTrabalhadorPortabilidadePaginado($params);

        return response()->json($response, $response['status']);
    }
}
