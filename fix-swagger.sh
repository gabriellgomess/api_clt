#!/bin/bash

##############################################################################
# Script para Corrigir Erro 404 no Swagger
#
# Sintoma: https://monbank.co/api/dataprev/documentation retorna 404
# Causa: L5-Swagger não publicado ou cache desatualizado
##############################################################################

set -e  # Exit on error

echo "========================================"
echo "🔧 Fix Swagger 404 - API Dataprev"
echo "========================================"
echo ""

# Cores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

APP_DIR="/var/www/html/dataprev"

# Verificar se está no diretório correto
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Erro: Execute este script do diretório raiz da aplicação${NC}"
    echo "cd /var/www/html/dataprev && ./fix-swagger.sh"
    exit 1
fi

echo -e "${BLUE}📂 Diretório: $APP_DIR${NC}"
echo ""

# 1. Verificar se o pacote L5-Swagger existe
echo -e "${YELLOW}[1/9] Verificando pacote L5-Swagger...${NC}"
if [ -d "vendor/darkaonline/l5-swagger" ]; then
    echo -e "${GREEN}✅ Pacote L5-Swagger encontrado${NC}"
else
    echo -e "${RED}❌ Pacote não encontrado. Executando composer install...${NC}"
    composer install --no-dev --optimize-autoloader
fi
echo ""

# 2. Publicar assets do L5-Swagger
echo -e "${YELLOW}[2/9] Publicando assets do L5-Swagger...${NC}"
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider" --force
echo ""

# 3. Limpar caches antigos
echo -e "${YELLOW}[3/9] Limpando caches antigos...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan clear-compiled
echo -e "${GREEN}✅ Caches limpos${NC}"
echo ""

# 4. Verificar se o diretório api-docs existe
echo -e "${YELLOW}[4/9] Verificando diretório storage/api-docs...${NC}"
if [ ! -d "storage/api-docs" ]; then
    echo -e "${YELLOW}⚠️  Criando diretório storage/api-docs${NC}"
    mkdir -p storage/api-docs
fi
echo ""

# 5. Ajustar permissões
echo -e "${YELLOW}[5/9] Ajustando permissões...${NC}"
chmod -R 755 storage/ bootstrap/cache/
chmod -R 775 storage/api-docs/
chown -R www-data:www-data storage/ bootstrap/cache/ 2>/dev/null || echo "⚠️  Não foi possível alterar owner (execute como root se necessário)"
echo -e "${GREEN}✅ Permissões ajustadas${NC}"
echo ""

# 6. Gerar documentação Swagger
echo -e "${YELLOW}[6/9] Gerando documentação Swagger...${NC}"
php artisan l5-swagger:generate
if [ -f "storage/api-docs/api-docs.json" ]; then
    FILE_SIZE=$(stat -f%z "storage/api-docs/api-docs.json" 2>/dev/null || stat -c%s "storage/api-docs/api-docs.json" 2>/dev/null)
    echo -e "${GREEN}✅ Documentação gerada: api-docs.json ($FILE_SIZE bytes)${NC}"
else
    echo -e "${RED}❌ Erro: Arquivo api-docs.json não foi gerado${NC}"
fi
echo ""

# 7. Recriar caches otimizados
echo -e "${YELLOW}[7/9] Recriando caches otimizados...${NC}"
php artisan config:cache
php artisan route:cache
echo -e "${GREEN}✅ Caches recriados${NC}"
echo ""

# 8. Verificar rotas registradas
echo -e "${YELLOW}[8/9] Verificando rotas do Swagger...${NC}"
ROUTES=$(php artisan route:list | grep -i "documentation\|swagger\|docs" || echo "")
if [ -z "$ROUTES" ]; then
    echo -e "${RED}❌ ATENÇÃO: Rotas do Swagger não foram encontradas!${NC}"
    echo ""
    echo "Possíveis soluções:"
    echo "1. Verificar se o provider está registrado em config/app.php"
    echo "2. Executar: php artisan optimize:clear"
    echo "3. Reiniciar o servidor web"
else
    echo -e "${GREEN}✅ Rotas encontradas:${NC}"
    echo "$ROUTES"
fi
echo ""

# 9. Teste final
echo -e "${YELLOW}[9/9] Testando endpoints...${NC}"
echo ""

# Testar endpoint de docs (JSON)
echo "Testando JSON: /api/dataprev/docs"
DOCS_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://monbank.co/api/dataprev/docs" 2>/dev/null || echo "000")
if [ "$DOCS_CODE" == "200" ]; then
    echo -e "${GREEN}✅ JSON docs respondendo (HTTP 200)${NC}"
else
    echo -e "${YELLOW}⚠️  JSON docs retornou HTTP $DOCS_CODE${NC}"
fi

# Testar endpoint de documentation (UI)
echo "Testando UI: /api/dataprev/documentation"
DOC_CODE=$(curl -s -o /dev/null -w "%{http_code}" "https://monbank.co/api/dataprev/documentation" 2>/dev/null || echo "000")
if [ "$DOC_CODE" == "200" ]; then
    echo -e "${GREEN}✅ Swagger UI respondendo (HTTP 200)${NC}"
else
    echo -e "${RED}❌ Swagger UI retornou HTTP $DOC_CODE${NC}"
fi

echo ""
echo "========================================"
echo -e "${GREEN}🎉 Script finalizado!${NC}"
echo "========================================"
echo ""

# Resultado final
if [ "$DOC_CODE" == "200" ]; then
    echo -e "${GREEN}✅ SUCESSO!${NC} Acesse:"
    echo "   https://monbank.co/api/dataprev/documentation"
else
    echo -e "${RED}❌ PROBLEMA DETECTADO${NC}"
    echo ""
    echo "Próximos passos:"
    echo "1. Verificar logs:"
    echo "   tail -50 storage/logs/laravel.log"
    echo ""
    echo "2. Verificar se o servidor web precisa ser reiniciado:"
    echo "   sudo systemctl restart apache2"
    echo "   # ou"
    echo "   sudo systemctl restart nginx"
    echo ""
    echo "3. Executar diagnóstico completo:"
    echo "   php artisan route:list | grep documentation"
    echo "   ls -lah storage/api-docs/"
    echo "   cat storage/logs/laravel.log | tail -20"
fi

echo ""
echo "📚 Documentação completa: FIX_SWAGGER_404.md"
