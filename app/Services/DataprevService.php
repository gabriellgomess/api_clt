<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DataprevService
{
    protected $apiUrl;
    protected $apiBasePath;
    protected $oauthUrl;
    protected $oauthClientId;
    protected $oauthClientSecret;
    protected $oauthUsername;
    protected $oauthPassword;
    protected $codigoSolicitante;
    protected $pfxPath;
    protected $pfxPassphrase;
    protected $tokenCacheTtl;

    public function __construct()
    {
        $this->apiUrl = config('dataprev.api_url', 'https://hapibancos.dataprev.gov.br');
        $this->apiBasePath = config('dataprev.api_base_path', '/e-consignado-trabalhador/v1.0.0');
        $this->oauthUrl = config('dataprev.oauth_url', 'https://hisrj.dataprev.gov.br');
        $this->oauthClientId = config('dataprev.oauth_client_id', '');
        $this->oauthClientSecret = config('dataprev.oauth_client_secret', '');
        $this->oauthUsername = config('dataprev.oauth_username', '');
        $this->oauthPassword = config('dataprev.oauth_password', '');
        $this->codigoSolicitante = config('dataprev.codigo_solicitante', '0526');
        $this->pfxPath = config('dataprev.pfx_path', '');
        $this->pfxPassphrase = config('dataprev.pfx_passphrase', '');
        $this->tokenCacheTtl = config('dataprev.token_cache_ttl', 3300);
    }

    /**
     * Obtém o token de acesso OAuth2
     * O token é cacheado para evitar requisições desnecessárias
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = 'dataprev_access_token';

        // Tenta obter do cache primeiro
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Requisita novo token
        $token = $this->requestNewToken();

        if ($token) {
            Cache::put($cacheKey, $token, $this->tokenCacheTtl);
        }

        return $token;
    }

    /**
     * Requisita um novo token OAuth2 da Dataprev
     * Usando cURL nativo pois o HTTP client do Laravel não passa corretamente as opções de certificado P12
     */
    protected function requestNewToken(): ?string
    {
        try {
            $endpoint = $this->oauthUrl . '/oauth2/token';

            // Monta o header Authorization Basic
            $basicAuth = base64_encode($this->oauthClientId . ':' . $this->oauthClientSecret);

            Log::info('Dataprev OAuth2 Token Request', [
                'endpoint' => $endpoint,
            ]);

            $curl = curl_init();

            $curlOptions = [
                CURLOPT_URL => $endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => 'grant_type=password&username=' . $this->oauthUsername . '&password=' . rawurlencode($this->oauthPassword),
                CURLOPT_HTTPHEADER => [
                    'Authorization: Basic ' . $basicAuth,
                    'Content-Type: application/x-www-form-urlencoded'
                ],
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ];

            // Adiciona certificado PFX se configurado
            if (!empty($this->pfxPath) && file_exists($this->pfxPath)) {
                $curlOptions[CURLOPT_SSLCERTTYPE] = 'P12';
                $curlOptions[CURLOPT_SSLCERT] = $this->pfxPath;
                $curlOptions[CURLOPT_KEYPASSWD] = $this->pfxPassphrase;

                Log::debug('Dataprev PFX Certificate Loaded for OAuth', [
                    'path' => $this->pfxPath,
                ]);
            }

            curl_setopt_array($curl, $curlOptions);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);

            curl_close($curl);

            Log::info('Dataprev OAuth2 Token Response', [
                'status' => $httpCode,
                'successful' => $httpCode >= 200 && $httpCode < 300,
            ]);

            if ($httpCode >= 200 && $httpCode < 300) {
                $data = json_decode($response, true);
                return $data['access_token'] ?? null;
            }

            Log::error('Dataprev OAuth2 Token Error', [
                'status' => $httpCode,
                'body' => $response,
                'curl_error' => $error,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Dataprev OAuth2 Token Exception', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Força a renovação do token (limpa o cache)
     */
    public function refreshToken(): ?string
    {
        Cache::forget('dataprev_access_token');
        return $this->getAccessToken();
    }

    /**
     * Inclusão de Proposta CTPS
     * POST {apiBasePath}/propostas-ctps/inclusao
     */
    public function incluirProposta(array $data): array
    {
        $endpoint = $this->apiBasePath . '/propostas-ctps/inclusao';

        // Adiciona o código do solicitante se não foi fornecido
        $data['codigoSolicitante'] = $data['codigoSolicitante'] ?? $this->codigoSolicitante;

        return $this->post($endpoint, $data);
    }

    /**
     * Inclusão de Proposta de Portabilidade CTPS
     * POST {apiBasePath}/propostas-portabilidade-ctps/inclusao
     */
    public function incluirPropostaPortabilidade(array $data): array
    {
        $endpoint = $this->apiBasePath . '/propostas-portabilidade-ctps/inclusao';

        // Adiciona o código do solicitante se não foi fornecido
        $data['codigoSolicitante'] = $data['codigoSolicitante'] ?? $this->codigoSolicitante;

        return $this->post($endpoint, $data);
    }

    /**
     * Consulta Solicitações do Trabalhador
     * GET {apiBasePath}/propostas-ctps/solicitacoes-trabalhador
     */
    public function consultarSolicitacoesTrabalhador(array $params = []): array
    {
        $endpoint = $this->apiBasePath . '/propostas-ctps/solicitacoes-trabalhador';

        // Adiciona o código do solicitante se não foi fornecido
        $params['codigoSolicitante'] = $params['codigoSolicitante'] ?? $this->codigoSolicitante;

        return $this->get($endpoint, $params);
    }

    /**
     * Consulta Solicitações do Trabalhador Paginado (Portabilidade)
     * GET {apiBasePath}/propostas-portabilidade-ctps/solicitacoes-trabalhador-paginado
     */
    public function consultarSolicitacoesTrabalhadorPortabilidadePaginado(array $params = []): array
    {
        $endpoint = $this->apiBasePath . '/propostas-portabilidade-ctps/solicitacoes-trabalhador-paginado';

        // Adiciona o código do solicitante se não foi fornecido
        $params['codigoSolicitante'] = $params['codigoSolicitante'] ?? $this->codigoSolicitante;

        return $this->get($endpoint, $params);
    }

    /**
     * Configura as opções do HTTP client incluindo mTLS com certificado PFX
     */
    protected function getHttpOptions(): array
    {
        $options = [
            'verify' => true,
            'curl' => [
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            ],
        ];

        // Adiciona certificado PFX se configurado
        if (!empty($this->pfxPath) && file_exists($this->pfxPath)) {
            $options['curl'][CURLOPT_SSLCERTTYPE] = 'P12';
            $options['curl'][CURLOPT_SSLCERT] = $this->pfxPath;

            if (!empty($this->pfxPassphrase)) {
                $options['curl'][CURLOPT_KEYPASSWD] = $this->pfxPassphrase;
            }

            Log::debug('Dataprev PFX Certificate Loaded', [
                'path' => $this->pfxPath,
            ]);
        } else {
            Log::warning('Dataprev PFX Certificate NOT loaded', [
                'path' => $this->pfxPath,
                'exists' => !empty($this->pfxPath) ? file_exists($this->pfxPath) : false,
            ]);
        }

        return $options;
    }

    /**
     * Realiza uma requisição POST para a API Dataprev
     */
    protected function post(string $endpoint, array $data): array
    {
        try {
            $token = $this->getAccessToken();

            if (!$token) {
                return [
                    'success' => false,
                    'status' => 401,
                    'data' => null,
                    'error' => 'Não foi possível obter o token de acesso OAuth2',
                ];
            }

            $response = Http::withToken($token)
                ->withOptions($this->getHttpOptions())
                ->timeout(30)
                ->post($this->apiUrl . $endpoint, $data);

            Log::info('Dataprev POST Request', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            // Se token expirou, tenta renovar e refazer a requisição
            if ($response->status() === 401) {
                Log::info('Dataprev Token expired, attempting refresh');
                $token = $this->refreshToken();

                if ($token) {
                    $response = Http::withToken($token)
                        ->withOptions($this->getHttpOptions())
                        ->timeout(30)
                        ->post($this->apiUrl . $endpoint, $data);
                }
            }

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Dataprev POST Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Realiza uma requisição GET para a API Dataprev
     */
    protected function get(string $endpoint, array $params = []): array
    {
        try {
            $token = $this->getAccessToken();

            if (!$token) {
                return [
                    'success' => false,
                    'status' => 401,
                    'data' => null,
                    'error' => 'Não foi possível obter o token de acesso OAuth2',
                ];
            }

            $response = Http::withToken($token)
                ->withOptions($this->getHttpOptions())
                ->timeout(30)
                ->get($this->apiUrl . $endpoint, $params);

            Log::info('Dataprev GET Request', [
                'endpoint' => $endpoint,
                'params' => $params,
                'status' => $response->status(),
            ]);

            // Se token expirou, tenta renovar e refazer a requisição
            if ($response->status() === 401) {
                Log::info('Dataprev Token expired, attempting refresh');
                $token = $this->refreshToken();

                if ($token) {
                    $response = Http::withToken($token)
                        ->withOptions($this->getHttpOptions())
                        ->timeout(30)
                        ->get($this->apiUrl . $endpoint, $params);
                }
            }

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('Dataprev GET Error', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'data' => null,
                'error' => $e->getMessage(),
            ];
        }
    }
}
