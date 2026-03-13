#!/bin/bash
# 🔧 Скрипт автоматического исправления docker-compose.yml для zumba-site
# Запускать на сервере от root

set -e

echo "========================================"
echo "🔧 Исправление docker-compose.yml"
echo "========================================"

cd ~/zumba-site

# Создать резервную копию
cp docker-compose.yml docker-compose.yml.bak
echo "✓ Резервная копия создана"

# 1. Изменить local.conf на default.conf
sed -i 's|docker/nginx/local.conf|docker/nginx/default.conf|g' docker-compose.yml
echo "✓ Конфиг nginx изменён на default.conf"

# 2. Изменить порт с 8080:80 на 80:80
sed -i 's|"8080:80"|"80:80"|g' docker-compose.yml
echo "✓ Порт 8080 изменён на 80"

# 3. Добавить порт 443 (если ещё не добавлен)
if ! grep -q '"443:443"' docker-compose.yml; then
    sed -i 's|"80:80"|"80:80"\n      - "443:443"|g' docker-compose.yml
    echo "✓ Порт 443 добавлен"
fi

# 4. Добавить volume для SSL (если ещё не добавлен)
if ! grep -q '/etc/letsencrypt' docker-compose.yml; then
    sed -i '/docker\/nginx\/default.conf/a\      - /etc/letsencrypt:/etc/letsencrypt:ro' docker-compose.yml
    echo "✓ SSL сертификаты добавлены"
fi

# 5. Удалить атрибут version (устарел)
sed -i '/^version:/d' docker-compose.yml
echo "✓ Атрибут version удалён"

echo ""
echo "========================================"
echo "📋 Итоговый docker-compose.yml:"
echo "========================================"
grep -A 20 "web:" docker-compose.yml

echo ""
echo "========================================"
echo "🚀 Перезапуск контейнеров..."
echo "========================================"

docker compose down
docker compose build --no-cache php bot
docker compose up -d

echo ""
echo "========================================"
echo "✅ Проверка статуса:"
echo "========================================"
docker compose ps

echo ""
echo "========================================"
echo "🌐 Проверка сайта:"
echo "========================================"
sleep 5
curl -I https://zumba-spb.ru 2>&1 | head -5 || echo "⚠️ Сайт ещё не доступен, подождите 30 секунд"

echo ""
echo "========================================"
echo "📝 Логи nginx (последние 20 строк):"
echo "========================================"
docker compose logs --tail=20 web

echo ""
echo "✅ Готово!"
echo "🌐 Сайт: https://zumba-spb.ru"
echo "⚙️ Админка: https://zumba-spb.ru/admin/"
