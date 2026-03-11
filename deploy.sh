#!/bin/bash

##############################################################################
# Script de Deploy - API Dataprev e-Consignado
#
# Este script automatiza o processo de deploy da aplicação no servidor
##############################################################################

set -e  # Exit on error

echo "================================================"
echo "🚀 Deploy - API Dataprev e-Consignado"
echo "================================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Diretório da aplicação
APP_DIR="/var/www/html/dataprev"

echo "📁 Diretório da aplicação: $APP_DIR"
echo ""

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: Este script deve ser executado do diretório raiz da aplicação Laravel${NC}"
    exit 1
fi

# 1. Modo de manutenção
echo -e "${YELLOW}🔧 Colocando aplicação em modo de manutenção...${NC}"
php artisan down || true

# 2. Backup (opcional)
echo -e "${YELLOW}💾 Criando backup...${NC}"
BACKUP_DIR="../dataprev_backup_$(date +%Y%m%d_%H%M%S)"
cp -r . "$BACKUP_DIR" 2>/dev/null || echo "⚠️  Backup pulado"

# 3. Git pull (se usar git)
if [ -d ".git" ]; then
    echo -e "${YELLOW}📥 Atualizando código do repositório...${NC}"
    git pull origin main
fi

# 4. Instalar/Atualizar dependências
echo -e "${YELLOW}📦 Instalando dependências do Composer...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction

# 5. Verificar/Criar .env
if [ ! -f ".env" ]; then
    echo -e "${YELLOW}⚙️  Criando arquivo .env...${NC}"
    if [ -f ".env.production" ]; then
        cp .env.production .env
    else
        echo -e "${RED}❌ Erro: Arquivo .env.production não encontrado${NC}"
        php artisan up
        exit 1
    fi
fi

# 6. Gerar chave da aplicação (se necessário)
if ! grep -q "APP_KEY=base64:" .env; then
    echo -e "${YELLOW}🔑 Gerando chave da aplicação...${NC}"
    php artisan key:generate --force
fi

# 7. Limpar caches antigos
echo -e "${YELLOW}🧹 Limpando caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 8. Otimizar aplicação
echo -e "${YELLOW}⚡ Otimizando aplicação...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Gerar documentação Swagger
echo -e "${YELLOW}📚 Gerando documentação Swagger...${NC}"
php artisan l5-swagger:generate

# 10. Verificar permissões
echo -e "${YELLOW}🔐 Configurando permissões...${NC}"
chmod -R 755 .
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "⚠️  Ajuste permissões manualmente se necessário"

# 11. Verificar certificado PFX
if [ -f "storage/certs/certificado.pfx" ]; then
    echo -e "${GREEN}✅ Certificado PFX encontrado${NC}"
    chmod 600 storage/certs/certificado.pfx
    chown www-data:www-data storage/certs/certificado.pfx 2>/dev/null || true
else
    echo -e "${RED}⚠️  ATENÇÃO: Certificado PFX não encontrado em storage/certs/certificado.pfx${NC}"
    echo "   Por favor, faça upload do certificado antes de usar a API"
fi

# 12. Tirar do modo de manutenção
echo -e "${YELLOW}✨ Tirando aplicação do modo de manutenção...${NC}"
php artisan up

echo ""
echo "================================================"
echo -e "${GREEN}✅ Deploy concluído com sucesso!${NC}"
echo "================================================"
echo ""

# 13. Testes básicos
echo "🧪 Executando testes básicos..."
echo ""

# Verificar se a aplicação responde
if command -v curl &> /dev/null; then
    echo "Testing: GET /api/dataprev/token"
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
        -X POST "https://monbank.co/api/dataprev/token" \
        -H "Authorization: Bearer tWuqmiEuQMYt8BxF3X3qJhXWO2vbBxrSuQAfxH98IRU3L8zD7jeMGULXjMpEAR3S" \
        2>/dev/null || echo "000")

    if [ "$HTTP_CODE" == "200" ] || [ "$HTTP_CODE" == "401" ]; then
        echo -e "${GREEN}✅ API respondendo (HTTP $HTTP_CODE)${NC}"
    else
        echo -e "${YELLOW}⚠️  API retornou HTTP $HTTP_CODE - verifique os logs${NC}"
    fi
else
    echo "⚠️  curl não instalado - pule os testes"
fi

echo ""
echo "📊 Status da aplicação:"
echo "   - Ambiente: production"
echo "   - URL: https://monbank.co/api/dataprev"
echo "   - Swagger: https://monbank.co/api/documentation"
echo "   - Logs: $APP_DIR/storage/logs/laravel.log"
echo ""

echo "📝 Próximos passos:"
echo "   1. Verificar logs: tail -f storage/logs/laravel.log"
echo "   2. Testar endpoints com Postman ou curl"
echo "   3. Verificar certificado PFX se necessário"
echo ""

echo -e "${GREEN}🎉 Deploy finalizado!${NC}"
