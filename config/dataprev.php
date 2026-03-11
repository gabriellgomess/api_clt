<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dataprev e-Consignado Configuration
    |--------------------------------------------------------------------------
    |
    | Configurações para integração com a API Dataprev e-Consignado.
    |
    */

    // URL base da API Dataprev (para endpoints de negócio)
    // Homolog: https://hapibancos.dataprev.gov.br | Prod: https://papibancos.dataprev.gov.br
    'api_url' => env('DATAPREV_API_URL', 'https://hapibancos.dataprev.gov.br'),

    // Base path dos endpoints da API
    // Homolog: /e-consignado-trabalhador/v1.0.0 | Prod: /e-consignado/v7.0.0
    'api_base_path' => env('DATAPREV_API_BASE_PATH', '/e-consignado-trabalhador/v1.0.0'),

    // URL do servidor de autenticação OAuth2
    'oauth_url' => env('DATAPREV_OAUTH_URL', 'https://hisrj.dataprev.gov.br'),

    // Credenciais OAuth2
    'oauth_client_id' => env('DATAPREV_OAUTH_CLIENT_ID', ''),
    'oauth_client_secret' => env('DATAPREV_OAUTH_CLIENT_SECRET', ''),
    'oauth_username' => env('DATAPREV_OAUTH_USERNAME', ''),
    'oauth_password' => env('DATAPREV_OAUTH_PASSWORD', ''),

    // Código do Solicitante (fornecido pela Dataprev)
    'codigo_solicitante' => env('DATAPREV_CODIGO_SOLICITANTE', '0526'),

    // Certificado PFX para autenticação mTLS
    'pfx_path' => env('DATAPREV_PFX_PATH', ''),
    'pfx_passphrase' => env('DATAPREV_PFX_PASSPHRASE', ''),

    // Tempo de cache do token em segundos (padrão: 55 minutos, token expira em 60)
    'token_cache_ttl' => env('DATAPREV_TOKEN_CACHE_TTL', 3300),
];
