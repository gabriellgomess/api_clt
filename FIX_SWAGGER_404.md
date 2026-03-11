# 🔧 Resolver Erro 404 no Swagger

## Sintoma
- URL: `https://monbank.co/api/dataprev/documentation`
- Erro: 404 do Laravel
- Log do Laravel: vazio (requisição não passa pelo controller)

## Causa Raiz
O pacote L5-Swagger não está registrado ou publicado corretamente.

## ✅ Solução - Execute no Servidor

### Passo 1: Verificar se o Pacote Está Instalado

```bash
cd /var/www/html/dataprev

# Verificar se o pacote existe
ls -la vendor/darkaonline/l5-swagger/
```

**Esperado:** Deve mostrar o diretório do pacote

---

### Passo 2: Publicar Assets e Config do L5-Swagger

```bash
cd /var/www/html/dataprev

# Publicar o provider do L5-Swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

# Quando perguntar, escolha a opção "all" (ou pressione Enter)
```

**Esperado:** Mensagens de arquivos publicados

---

### Passo 3: Verificar se Config Foi Publicado

```bash
# Verificar se o arquivo de config existe
ls -la config/l5-swagger.php

# Verificar se as views foram publicadas
ls -la resources/views/vendor/l5-swagger/
```

**Esperado:** Arquivos devem existir

---

### Passo 4: Limpar Todos os Caches

```bash
cd /var/www/html/dataprev

# Limpar cache de configuração
php artisan config:clear

# Limpar cache de rotas
php artisan route:clear

# Limpar cache de views
php artisan view:clear

# Limpar cache geral
php artisan cache:clear

# Limpar compiled classes
php artisan clear-compiled
```

---

### Passo 5: Recriar Caches Otimizados

```bash
cd /var/www/html/dataprev

# Cachear configuração
php artisan config:cache

# Cachear rotas
php artisan route:cache

# Gerar documentação Swagger
php artisan l5-swagger:generate
```

---

### Passo 6: Verificar se as Rotas do Swagger Existem

```bash
cd /var/www/html/dataprev

# Listar todas as rotas e filtrar pelo Swagger
php artisan route:list | grep -i swagger
```

**Esperado:** Deve mostrar as rotas:
```
GET|HEAD  api/dataprev/documentation ........ l5-swagger.default.api
GET|HEAD  api/dataprev/docs ................. l5-swagger.default.docs
```

---

### Passo 7: Verificar Permissões

```bash
cd /var/www/html/dataprev

# Ajustar permissões
chmod -R 755 storage/ bootstrap/cache/
chmod -R 775 storage/api-docs/

chown -R www-data:www-data storage/ bootstrap/cache/
```

---

### Passo 8: Testar Novamente

```bash
# Testar se o arquivo JSON da documentação foi gerado
ls -lah storage/api-docs/api-docs.json

# Ver o tamanho (deve ter alguns KB)
cat storage/api-docs/api-docs.json | wc -c
```

Depois acesse no navegador:
```
https://monbank.co/api/dataprev/documentation
```

---

## 🐛 Se Ainda Não Funcionar

### Verificação 1: Provider Registrado

Verifique se o provider está no `config/app.php`:

```bash
grep -n "L5SwaggerServiceProvider" config/app.php
```

**Se não aparecer nada**, adicione manualmente no `config/app.php`:

```php
'providers' => [
    // ... outros providers
    L5Swagger\L5SwaggerServiceProvider::class,
],
```

### Verificação 2: Rota Registrada Manualmente

Se o problema persistir, adicione a rota manualmente no `routes/web.php`:

```bash
nano routes/web.php
```

Adicione no final:

```php
// Rota manual do Swagger (se necessário)
Route::get('/api/dataprev/documentation', function () {
    return view('l5-swagger::index', [
        'documentation' => config('l5-swagger.documentations.default'),
        'secure' => request()->secure(),
    ]);
});
```

Depois:
```bash
php artisan route:clear
php artisan route:cache
```

### Verificação 3: Arquivo de Documentação

```bash
# Verificar se o JSON existe e é válido
cat storage/api-docs/api-docs.json | jq . | head -20

# Se jq não estiver instalado, use:
cat storage/api-docs/api-docs.json | head -100
```

---

## 📋 Checklist Completo

Execute cada comando abaixo e marque se funcionou:

```bash
cd /var/www/html/dataprev

# 1. Publicar L5-Swagger
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force

# 2. Limpar caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 3. Recriar caches
php artisan config:cache
php artisan route:cache

# 4. Gerar documentação
php artisan l5-swagger:generate

# 5. Permissões
chmod -R 775 storage/api-docs/
chown -R www-data:www-data storage/

# 6. Verificar rotas
php artisan route:list | grep documentation

# 7. Verificar arquivo gerado
ls -lah storage/api-docs/api-docs.json

# 8. Reiniciar servidor web (se necessário)
sudo systemctl restart apache2
# ou
sudo systemctl restart nginx
```

---

## 🔍 Logs para Verificar

### 1. Log do Laravel
```bash
tail -50 /var/www/html/dataprev/storage/logs/laravel.log
```

### 2. Log do Apache/Nginx
```bash
# Apache
sudo tail -50 /var/log/apache2/error.log

# Nginx
sudo tail -50 /var/log/nginx/error.log
```

### 3. Log de Acesso
```bash
# Apache
sudo tail -50 /var/log/apache2/access.log | grep dataprev

# Nginx
sudo tail -50 /var/log/nginx/access.log | grep dataprev
```

---

## ✅ Teste Final

Após executar todos os passos, teste:

### 1. Testar via curl
```bash
curl -I https://monbank.co/api/dataprev/documentation
```

**Esperado:** `HTTP/2 200`

### 2. Testar o JSON
```bash
curl https://monbank.co/api/dataprev/docs
```

**Esperado:** JSON da documentação

### 3. Testar no navegador
```
https://monbank.co/api/dataprev/documentation
```

**Esperado:** Interface do Swagger UI carregada

---

## 📞 Se Precisar de Mais Ajuda

Envie a saída destes comandos:

```bash
cd /var/www/html/dataprev

echo "=== COMPOSER PACKAGES ==="
composer show | grep swagger

echo "=== ROTAS ==="
php artisan route:list | grep -E "(documentation|swagger|docs)"

echo "=== CONFIG ==="
php artisan config:show l5-swagger | head -20

echo "=== ARQUIVO JSON ==="
ls -lah storage/api-docs/

echo "=== PERMISSÕES ==="
ls -la storage/ | grep api-docs

echo "=== ULTIMA LINHA DO LOG ==="
tail -5 storage/logs/laravel.log
```

---

**Última atualização:** 10/02/2026
