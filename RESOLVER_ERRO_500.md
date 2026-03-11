# 🚨 Resolver Erro 500 - Swagger

## Situação Atual
- ✅ Outras rotas da API funcionam
- ❌ `/api/dataprev/documentation` retorna erro 500
- ❌ Log do Laravel vazio (`storage/logs/laravel.log`)

## 🔧 Solução Rápida

### Passo 1: Habilitar Debug (via WinSCP)

1. **Fazer backup do .env atual:**
   - No WinSCP, renomear `.env` para `.env.backup`

2. **Copiar arquivo de debug:**
   - No WinSCP, copiar `.env.debug` para o servidor
   - Renomear `.env.debug` para `.env`

3. **Limpar cache no servidor:**
   ```bash
   cd /var/www/html/dataprev
   php artisan config:clear
   ```

### Passo 2: Executar Comandos no Servidor (SSH)

```bash
cd /var/www/html/dataprev

# 1. Limpar todos os caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 2. Regenerar autoload
composer dump-autoload --optimize

# 3. Publicar L5-Swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force

# 4. Gerar documentação
php artisan l5-swagger:generate

# 5. Recriar caches
php artisan config:cache
php artisan route:cache
```

### Passo 3: Testar e Ver o Erro Real

```bash
# Testar a URL
curl https://monbank.co/api/dataprev/documentation

# Ver o log (agora deve aparecer o erro)
tail -50 /var/www/html/dataprev/storage/logs/laravel.log
```

### Passo 4: Ver Log do Apache

```bash
# Ver erros do Apache
sudo tail -50 /var/log/apache2/error.log | grep -A 5 dataprev
```

## 🔍 Problemas Comuns e Soluções

### Problema 1: Erro de Sintaxe PHP

**Sintoma:** Log mostra "Parse error" ou "Syntax error"

**Solução:**
```bash
# Verificar sintaxe
php -l config/l5-swagger.php
```

Se houver erro, o arquivo foi corrompido no upload. Re-upload via WinSCP em modo **texto** (não binário).

---

### Problema 2: Classe L5Swagger Não Encontrada

**Sintoma:** Log mostra "Class 'L5Swagger\L5SwaggerServiceProvider' not found"

**Solução:**
```bash
cd /var/www/html/dataprev

# Verificar se o pacote existe
ls -la vendor/darkaonline/l5-swagger/

# Se não existir, reinstalar
composer install --no-dev --optimize-autoloader
```

---

### Problema 3: Permissões

**Sintoma:** Log mostra "Permission denied" ou "failed to open stream"

**Solução:**
```bash
cd /var/www/html/dataprev

# Ajustar permissões
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

### Problema 4: Views do L5-Swagger Não Publicadas

**Sintoma:** Log mostra "View [l5-swagger::index] not found"

**Solução:**
```bash
cd /var/www/html/dataprev

# Forçar publicação das views
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --tag=views --force

# Limpar cache de views
php artisan view:clear
```

---

### Problema 5: Configuração do L5-Swagger Incorreta

**Sintoma:** Erro 500 sem mensagem clara no log

**Solução:**
```bash
cd /var/www/html/dataprev

# Publicar configuração padrão
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --tag=config --force

# Editar e verificar config/l5-swagger.php
nano config/l5-swagger.php
```

Verificar se a linha 15 está assim:
```php
'api' => 'api/dataprev/documentation',
```

---

## 📋 Checklist de Verificação

Execute no servidor e marque o que funciona:

```bash
cd /var/www/html/dataprev

# [ ] 1. Verificar se o pacote está instalado
ls vendor/darkaonline/l5-swagger/

# [ ] 2. Verificar sintaxe do config
php -l config/l5-swagger.php

# [ ] 3. Verificar se as views existem
ls resources/views/vendor/l5-swagger/

# [ ] 4. Verificar se o JSON foi gerado
ls -lah storage/api-docs/api-docs.json

# [ ] 5. Verificar rotas
php artisan route:list | grep documentation

# [ ] 6. Verificar permissões
ls -la storage/logs/

# [ ] 7. Testar geração do Swagger
php artisan l5-swagger:generate

# [ ] 8. Ver log de erro
tail -20 storage/logs/laravel.log
```

---

## 🆘 Comandos All-in-One

Execute tudo de uma vez:

```bash
cd /var/www/html/dataprev

# Limpar tudo
php artisan optimize:clear

# Publicar L5-Swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force

# Regenerar
composer dump-autoload --optimize
php artisan l5-swagger:generate

# Recriar caches
php artisan config:cache
php artisan route:cache

# Permissões
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Testar
php artisan route:list | grep documentation
ls -lah storage/api-docs/api-docs.json
tail -20 storage/logs/laravel.log
```

---

## 📤 Me Envie Essas Informações

Para eu te ajudar melhor, execute e me envie a saída:

```bash
cd /var/www/html/dataprev

echo "=== COMPOSER PACKAGES ==="
composer show | grep swagger

echo ""
echo "=== CONFIG L5-SWAGGER ==="
php artisan config:show l5-swagger 2>&1 | head -30

echo ""
echo "=== ROTAS ==="
php artisan route:list | grep -E "(documentation|swagger|docs)"

echo ""
echo "=== ARQUIVO API-DOCS ==="
ls -lah storage/api-docs/

echo ""
echo "=== VIEWS PUBLICADAS ==="
ls -la resources/views/vendor/l5-swagger/ 2>&1

echo ""
echo "=== ÚLTIMAS 20 LINHAS DO LOG ==="
tail -20 storage/logs/laravel.log

echo ""
echo "=== LOG APACHE ==="
sudo tail -20 /var/log/apache2/error.log | grep -i dataprev
```

---

## 🔄 Voltar ao Normal (Desabilitar Debug)

Quando resolver o problema, volte o .env para produção:

```bash
cd /var/www/html/dataprev

# Via WinSCP: renomear .env.backup para .env
# Ou copiar .env.production para .env

# Limpar cache
php artisan config:clear
php artisan config:cache
```

---

**Criado em:** 10/02/2026
**Versão:** 1.0
