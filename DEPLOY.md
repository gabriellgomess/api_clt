# 🚀 Guia de Deploy - API Dataprev e-Consignado

Este guia explica como fazer o deploy da aplicação API Dataprev no servidor monbank.co que já possui outras aplicações Laravel e React.

## 📁 Estrutura no Servidor

```
/var/www/html/
├── backend/              (Laravel existente)
│   └── public/
├── abrasuaconta/        (Frontend)
├── dataprev/            (NOVA aplicação Laravel Dataprev)
│   ├── app/
│   ├── bootstrap/
│   ├── config/
│   ├── public/
│   │   ├── .htaccess
│   │   └── index.php
│   ├── storage/
│   │   ├── certs/
│   │   │   └── certificado.pfx
│   │   ├── api-docs/
│   │   ├── app/
│   │   ├── framework/
│   │   └── logs/
│   ├── vendor/
│   ├── .env
│   └── composer.json
├── storage/             (Storage público)
├── .htaccess            (Raiz - ATUALIZAR)
└── index.html           (Frontend principal)
```

## 🔧 Passos para Deploy

### 1. Fazer Upload dos Arquivos

```bash
# Fazer upload via FTP/SFTP ou rsync
# Upload do diretório dataprev para /var/www/html/dataprev
```

### 2. Atualizar .htaccess da Raiz

Substitua o `.htaccess` na raiz (`/var/www/html/.htaccess`) pelo conteúdo abaixo:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    # Redirecionar tudo do domínio antigo para o novo caminho
    RewriteCond %{HTTP_HOST} ^abrasuaconta\.monbank\.co$ [NC]
    RewriteRule ^(.*)$ https://monbank.co/abrasuaconta/$1 [L,R=301]

    # 1. Permite o acesso direto a arquivos no diretório /storage
    RewriteCond %{REQUEST_URI} ^/storage/ [NC]
    RewriteRule ^ - [L]

    # 2. Ignora URLs que são arquivos ou diretórios existentes
    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    # 3. NOVO: Redirecionar URLs da API Dataprev para a aplicação Laravel separada
    # Rotas: /api/dataprev/* -> /dataprev/public/api/dataprev/*
    RewriteCond %{REQUEST_URI} ^/api/dataprev [NC]
    RewriteRule ^api/dataprev/(.*)$ /dataprev/public/api/dataprev/$1 [L,QSA]

    # 4. Redirecionar URLs que começam com /api para o backend Laravel principal
    RewriteCond %{REQUEST_URI} ^/api [NC]
    RewriteRule ^api/(.*)$ /backend/public/api/$1 [L,QSA]

    # 5. Redirecionar URLs do API Gateway para a pasta correta
    RewriteCond %{REQUEST_URI} ^/api-gateway [NC]
    RewriteRule ^api-gateway/(.*)$ /api-gateway/public/$1 [L,QSA]

    # 6. Roteamento padrão do Laravel (para rotas específicas do backend)
    RewriteCond %{REQUEST_URI} ^/backend [NC]
    RewriteRule ^backend/(.*)$ /backend/public/$1 [L,QSA]

    # 7. Redirecionar todas as outras rotas para o index.html do React
    RewriteRule ^ index.html [L]
</IfModule>
```

**IMPORTANTE**: A ordem das regras é crucial! A regra da Dataprev (item 3) deve vir ANTES da regra genérica do /api (item 4).

### 3. Configurar Permissões no Servidor

```bash
# Acesse o servidor via SSH
cd /var/www/html/dataprev

# Configurar permissões
sudo chown -R www-data:www-data .
sudo chmod -R 755 .

# Permissões especiais para storage e bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### 4. Configurar o Arquivo .env

```bash
# Copiar o arquivo .env de produção
cp .env.production .env

# Editar o .env se necessário
nano .env
```

**Verificar as configurações importantes:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://monbank.co

# Certificado PFX - Ajustar o caminho se necessário
DATAPREV_PFX_PATH="/var/www/html/dataprev/storage/certs/certificado.pfx"

# Swagger
L5_SWAGGER_GENERATE_ALWAYS=false
L5_SWAGGER_CONST_HOST=https://monbank.co/api
```

### 5. Instalar Dependências

```bash
cd /var/www/html/dataprev

# Instalar dependências do Composer
composer install --optimize-autoloader --no-dev

# Gerar chave da aplicação (se necessário)
php artisan key:generate

# Limpar caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Gerar documentação do Swagger
php artisan l5-swagger:generate
```

### 6. Verificar Certificado PFX

```bash
# Verificar se o certificado existe
ls -la /var/www/html/dataprev/storage/certs/certificado.pfx

# Se não existir, fazer upload do certificado
# Permissões do certificado
chmod 600 /var/www/html/dataprev/storage/certs/certificado.pfx
chown www-data:www-data /var/www/html/dataprev/storage/certs/certificado.pfx
```

### 7. Testar a Aplicação

```bash
# Testar se o Laravel está respondendo
curl -I https://monbank.co/api/dataprev/token
```

## 🌐 URLs da API em Produção

### Documentação Swagger
```
https://monbank.co/api/dataprev/documentation
```

### Endpoints da API

**Autenticação (Debug/Teste)**
```
POST https://monbank.co/api/dataprev/token
```

**Inclusão de Proposta CTPS**
```
POST https://monbank.co/api/dataprev/propostas/inclusao
```

**Consulta Solicitações do Trabalhador**
```
GET https://monbank.co/api/dataprev/propostas/solicitacoes-trabalhador
```

**Inclusão de Proposta de Portabilidade CTPS**
```
POST https://monbank.co/api/dataprev/propostas-portabilidade/inclusao
```

**Consulta Solicitações Paginado (Portabilidade)**
```
GET https://monbank.co/api/dataprev/propostas-portabilidade/solicitacoes-trabalhador-paginado
```

## 🔐 Autenticação

Todas as requisições devem incluir o header:

```
Authorization: Bearer tWuqmiEuQMYt8BxF3X3qJhXWO2vbBxrSuQAfxH98IRU3L8zD7jeMGULXjMpEAR3S
```

## 🧪 Testando a Integração

### Teste 1: Verificar se a rota está funcionando

```bash
curl -X GET \
  'https://monbank.co/api/dataprev/propostas/solicitacoes-trabalhador?codigoSolicitante=0526' \
  -H 'Authorization: Bearer tWuqmiEuQMYt8BxF3X3qJhXWO2vbBxrSuQAfxH98IRU3L8zD7jeMGULXjMpEAR3S'
```

**Resposta esperada:**
```json
{
  "success": true,
  "status": 200,
  "data": [...]
}
```

### Teste 2: Verificar os logs

```bash
# Verificar logs de erro
tail -f /var/www/html/dataprev/storage/logs/laravel.log

# Verificar logs do Apache
sudo tail -f /var/log/apache2/error.log
```

## 🚨 Troubleshooting

### Erro 500 - Internal Server Error

1. Verificar permissões do storage:
```bash
sudo chmod -R 775 storage bootstrap/cache
sudo chown -R www-data:www-data storage bootstrap/cache
```

2. Verificar o arquivo .env:
```bash
cat .env
```

3. Verificar logs:
```bash
tail -50 storage/logs/laravel.log
```

### Erro 404 - Not Found

1. Verificar se o `.htaccess` da raiz está correto
2. Verificar se o mod_rewrite está habilitado:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Token OAuth2 não funciona

1. Verificar se o certificado .pfx existe:
```bash
ls -la storage/certs/certificado.pfx
```

2. Verificar permissões do certificado:
```bash
chmod 600 storage/certs/certificado.pfx
chown www-data:www-data storage/certs/certificado.pfx
```

3. Verificar os logs:
```bash
grep "Dataprev" storage/logs/laravel.log
```

### Certificado PFX não carrega

1. Verificar se a extensão OpenSSL está instalada:
```bash
php -m | grep openssl
```

2. Se não estiver, instalar:
```bash
sudo apt-get install php-openssl
sudo systemctl restart apache2
```

## 📊 Monitoramento

### Verificar cache do token OAuth2

```bash
# Limpar cache se necessário
php artisan cache:clear
```

### Verificar espaço em disco

```bash
df -h
du -sh storage/logs/
```

### Rotação de logs

Adicionar ao crontab para limpar logs antigos:

```bash
# Editar crontab
crontab -e

# Adicionar (limpar logs com mais de 7 dias)
0 0 * * * find /var/www/html/dataprev/storage/logs -name "*.log" -mtime +7 -delete
```

## 🔄 Atualizações Futuras

Para atualizar a aplicação:

```bash
cd /var/www/html/dataprev

# Fazer backup
cp -r ../dataprev ../dataprev_backup_$(date +%Y%m%d_%H%M%S)

# Fazer upload dos novos arquivos
# Depois executar:

composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan l5-swagger:generate

# Verificar permissões
sudo chown -R www-data:www-data storage bootstrap/cache
```

## 📝 Notas Importantes

1. **Segurança**: O token Bearer está hardcoded. Considere usar variáveis de ambiente mais seguras em produção.

2. **HTTPS**: Certifique-se de que o certificado SSL está configurado corretamente.

3. **Logs**: Em produção, `APP_DEBUG=false` e `LOG_LEVEL=error` para evitar logs desnecessários.

4. **Cache**: O token OAuth2 é cacheado por 55 minutos. Se precisar forçar renovação, limpe o cache.

5. **Backup**: Sempre faça backup antes de atualizar a aplicação.

## ✅ Checklist de Deploy

- [ ] Upload dos arquivos para `/var/www/html/dataprev`
- [ ] Atualizar `.htaccess` da raiz
- [ ] Configurar permissões (755 geral, 775 storage/cache)
- [ ] Copiar e configurar `.env` de produção
- [ ] Fazer upload do certificado `.pfx`
- [ ] Executar `composer install --no-dev`
- [ ] Executar `php artisan config:cache`
- [ ] Executar `php artisan route:cache`
- [ ] Executar `php artisan l5-swagger:generate`
- [ ] Testar endpoints com curl
- [ ] Verificar logs
- [ ] Documentar URL da API para o time

---

**Deploy realizado em:** _____________

**Por:** _____________

**Status:** _____________
