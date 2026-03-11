# 📚 URLs do Swagger - API Dataprev

## 🌐 Em Produção (Servidor monbank.co)

### Documentação Swagger UI
```
https://monbank.co/api/dataprev/documentation
```

### Arquivo JSON da Documentação
```
https://monbank.co/api/dataprev/docs
```

## 💻 Em Desenvolvimento Local

### Documentação Swagger UI
```
http://localhost:8000/api/dataprev/documentation
```

### Arquivo JSON da Documentação
```
http://localhost:8000/api/dataprev/docs
```

## 🔧 Como Funciona o Roteamento

### Fluxo no Servidor

1. **Requisição do navegador:**
   ```
   https://monbank.co/api/dataprev/documentation
   ```

2. **`.htaccess` da raiz redireciona para:**
   ```
   /dataprev/public/api/dataprev/documentation
   ```

3. **Laravel (dataprev) processa:**
   ```
   Route: api/dataprev/documentation
   Controller: SwaggerController@api
   ```

4. **Resposta:**
   ```
   Interface Swagger UI renderizada
   ```

## ⚙️ Configuração

### Arquivo: `config/l5-swagger.php`

```php
'routes' => [
    'api' => 'api/dataprev/documentation',  // ← Rota configurada
],
```

### Arquivo: `.env` (Produção)

```env
L5_SWAGGER_CONST_HOST=https://monbank.co/api/dataprev
```

### Arquivo: `.env` (Local)

```env
L5_SWAGGER_CONST_HOST=/api/dataprev
```

## 🎯 Por Que Usar `/api/dataprev/documentation`?

### Problema Evitado

Se usássemos apenas `/api/documentation`, ocorreria:

```
Requisição: https://monbank.co/api/documentation
              ↓
.htaccess vê /api (genérico)
              ↓
Redireciona para: /backend/public/api/documentation
              ↓
❌ Vai para o backend ERRADO, não para dataprev!
```

### Solução Implementada

Usando `/api/dataprev/documentation`:

```
Requisição: https://monbank.co/api/dataprev/documentation
              ↓
.htaccess vê /api/dataprev (específico)
              ↓
Redireciona para: /dataprev/public/api/dataprev/documentation
              ↓
✅ Vai para a aplicação dataprev CORRETA!
```

## 📋 Checklist Pós-Deploy

Após fazer deploy no servidor, verifique:

- [ ] Acesse https://monbank.co/api/dataprev/documentation
- [ ] Verifica se a interface Swagger carrega
- [ ] Verifica se os endpoints estão listados
- [ ] Testa o botão "Try it out" em algum endpoint
- [ ] Verifica se o token Bearer é aceito

## 🐛 Troubleshooting

### Erro 404 - Not Found

**Causa:** O .htaccess da raiz não está redirecionando corretamente.

**Solução:**
```bash
# Verificar se a regra está presente em /var/www/html/.htaccess
grep "api/dataprev" /var/www/html/.htaccess

# Deve aparecer:
# RewriteCond %{REQUEST_URI} ^/api/dataprev [NC]
# RewriteRule ^api/dataprev/(.*)$ /dataprev/public/api/dataprev/$1 [L,QSA]
```

### Swagger não carrega (página em branco)

**Causa:** Documentação não foi gerada.

**Solução:**
```bash
cd /var/www/html/dataprev
php artisan l5-swagger:generate
```

### Assets do Swagger não carregam (CSS/JS)

**Causa:** Permissões ou caminho incorreto.

**Solução:**
```bash
# Verificar permissões
chmod -R 755 vendor/swagger-api/swagger-ui/dist/

# Verificar se arquivos existem
ls -la vendor/swagger-api/swagger-ui/dist/
```

### Endpoints não aparecem na documentação

**Causa:** Anotações OpenAPI não foram escaneadas.

**Solução:**
```bash
# Limpar cache e regenerar
php artisan config:clear
php artisan l5-swagger:generate
```

## 📝 Exemplo de Uso

### 1. Acessar o Swagger

Abra no navegador:
```
https://monbank.co/api/dataprev/documentation
```

### 2. Autorizar com Bearer Token

1. Clique no botão **"Authorize"** (cadeado no topo)
2. Cole o token:
   ```
   tWuqmiEuQMYt8BxF3X3qJhXWO2vbBxrSuQAfxH98IRU3L8zD7jeMGULXjMpEAR3S
   ```
3. Clique em **"Authorize"**
4. Clique em **"Close"**

### 3. Testar um Endpoint

1. Expanda o endpoint **GET /api/dataprev/propostas/solicitacoes-trabalhador**
2. Clique em **"Try it out"**
3. Preencha os parâmetros (ex: `codigoSolicitante: 0526`)
4. Clique em **"Execute"**
5. Veja a resposta abaixo

## ✅ URLs Finais - Resumo

| Ambiente | Swagger UI | JSON Docs |
|----------|------------|-----------|
| **Produção** | https://monbank.co/api/dataprev/documentation | https://monbank.co/api/dataprev/docs |
| **Local** | http://localhost:8000/api/dataprev/documentation | http://localhost:8000/api/dataprev/docs |

---

**Última atualização:** 10/02/2026
