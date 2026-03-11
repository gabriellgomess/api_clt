# 🏦 Dataprev e-Consignado API

API Laravel dedicada para integração com o sistema Dataprev e-Consignado para operações de consignado CTPS.

## 📋 Índice

- [Sobre](#sobre)
- [Instalação Local](#instalação-local)
- [Deploy em Produção](#deploy-em-produção)
- [Configuração](#configuração)
- [Documentação da API](#documentação-da-api)
- [Rotas Principais](#rotas-principais)
- [Arquitetura](#arquitetura)

## 🎯 Sobre

Esta aplicação é um microsserviço Laravel isolado que:

- ✅ Obtém automaticamente tokens OAuth2 da Dataprev
- ✅ Cacheia tokens por 55 minutos
- ✅ Usa certificado mTLS (PFX) para autenticação
- ✅ Renova tokens automaticamente quando expiram
- ✅ Fornece documentação Swagger completa
- ✅ Valida autenticação do cliente via Bearer token

## 🚀 Instalação Local

### Requisitos

- PHP 8.1+
- Composer
- Extensão OpenSSL do PHP
- Certificado .pfx da Dataprev

### Passos

1.  **Clone ou copie os arquivos**
    ```bash
    cd /caminho/do/projeto
    ```

2.  **Instale as dependências PHP:**
    ```bash
    composer install
    ```

3.  **Configure o ambiente:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configure o certificado** (veja seção abaixo)

5.  **Inicie o servidor de desenvolvimento:**
    ```bash
    php artisan serve
    ```

6.  **Acesse a documentação:**
    ```
    http://localhost:8000/api/dataprev/documentation
    ```

## Configuração do Certificado

Para que a autenticação na Dataprev funcione, você precisa do certificado digital (arquivo `.pfx`):

1.  Crie a pasta para armazenar o certificado (se não existir):
    ```bash
    mkdir -p storage/certs
    ```
2.  **Copie o seu arquivo `certificado.pfx` para dentro desta pasta.**
    Certifique-se de que o caminho no `.env` está correto:
    ```env
    DATAPREV_PFX_PATH=/var/www/html/dataprev/storage/certs/certificado.pfx
    ```
    *(Ajuste o caminho absoluto conforme o servidor onde for implantado)*

3.  Verifique as permissões de leitura do arquivo para o usuário do servidor web (ex: `www-data` ou `apache`).

## Documentação da API (Swagger)

A documentação da API pode ser acessada em:
-   **URL:** `/api/dataprev/documentation`
-   Para regenerar a documentação:
    ```bash
    php artisan l5-swagger:generate
    ```

## 🛣️ Rotas Principais

Todas as rotas da API estão prefixadas com `/api/dataprev` e protegidas pelo token de cliente (`DATAPREV_CLIENT_TOKEN` no .env).

### Autenticação

```http
POST /api/dataprev/token
```
Obtém/renova token OAuth2 da Dataprev (endpoint de debug/teste)

### Propostas CTPS

```http
POST /api/dataprev/propostas/inclusao
```
Incluir uma nova proposta de consignado CTPS

```http
GET /api/dataprev/propostas/solicitacoes-trabalhador
```
Consultar solicitações de propostas do trabalhador

**Parâmetros de consulta:**
- `codigoSolicitante` - Código do solicitante (opcional, padrão: 0526)
- `idSolicitacaoProposta` - ID específico (retorna 1 registro)
- `nroPagina` - Número da página
- `dataHoraInicio` - Filtro por período (DDMMYYYYHHmmss)
- `dataHoraFim` - Filtro por período (DDMMYYYYHHmmss)

### Portabilidade CTPS

```http
POST /api/dataprev/propostas-portabilidade/inclusao
```
Incluir proposta de portabilidade

```http
GET /api/dataprev/propostas-portabilidade/solicitacoes-trabalhador-paginado
```
Consultar solicitações de portabilidade (paginado)

## 🏗️ Arquitetura

### Fluxo de Requisição

```
Cliente/Frontend
    ↓
    [Bearer Token do Cliente]
    ↓
VerifyDataprevToken (Middleware)
    ↓
DataprevController
    ↓
DataprevService
    ↓
    ├─→ getAccessToken() → [Cache ou OAuth2]
    │                           ↓
    │                       [Certificado PFX + OAuth2]
    │                           ↓
    │                       Dataprev OAuth Server
    ↓
[Token OAuth2 cacheado 55min]
    ↓
Requisição para API Dataprev
    ↓
[Certificado PFX + Bearer OAuth2]
    ↓
API Dataprev e-Consignado
    ↓
Resposta formatada
```

### Componentes Principais

| Componente | Descrição |
|------------|-----------|
| **VerifyDataprevToken** | Middleware que valida o Bearer token do cliente |
| **DataprevController** | Controller com endpoints da API |
| **DataprevService** | Service que gerencia OAuth2 e chamadas à Dataprev |
| **Cache** | Sistema de cache para tokens OAuth2 (55min) |
| **PFX Certificate** | Certificado mTLS para autenticação |

### Segurança

1. **Autenticação Dupla:**
   - Cliente → API: Bearer token configurado no `.env`
   - API → Dataprev: OAuth2 + Certificado mTLS

2. **Cache Seguro:**
   - Tokens OAuth2 cacheados em filesystem
   - Renovação automática quando expiram

3. **Certificado mTLS:**
   - Armazenado com permissões restritas (600)
   - Usado em todas as chamadas OAuth2 e API

## 🌐 Deploy em Produção

### Deploy no Servidor monbank.co

Este projeto roda em um servidor que já possui outras aplicações. A aplicação é instalada em `/var/www/html/dataprev` e acessada via `https://monbank.co/api/dataprev/*`.

#### Estrutura no Servidor

```
/var/www/html/
├── backend/         (Laravel existente - outras APIs)
├── dataprev/        (Esta aplicação)
├── abrasuaconta/    (Frontend React)
└── .htaccess        (Configuração de rotas)
```

#### Deploy Automático

1. Faça upload dos arquivos para `/var/www/html/dataprev`
2. Execute o script de deploy:
   ```bash
   cd /var/www/html/dataprev
   chmod +x deploy.sh
   ./deploy.sh
   ```

#### Deploy Manual

Veja o guia completo em [DEPLOY.md](./DEPLOY.md) para instruções detalhadas.

### URLs em Produção

- **API Base:** `https://monbank.co/api/dataprev`
- **Swagger:** `https://monbank.co/api/documentation`

## ⚙️ Configuração

### Arquivo .env

Para ambiente de **produção**, use `.env.production` como template:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://monbank.co

DATAPREV_PFX_PATH="/var/www/html/dataprev/storage/certs/certificado.pfx"
DATAPREV_CLIENT_TOKEN=seu-token-aqui
```

Para ambiente de **desenvolvimento local**, use `.env.example`.
