#!/bin/bash
# Скрипт деплоя Zumba Site на сервер

set -e

echo "🚀 Начало деплоя Zumba Site..."

# Переход в директорию проекта
cd /root/zumba-site || { echo "❌ Директория не найдена"; exit 1; }

# Обновление кода
echo "📦 Обновление кода из репозитория..."
git pull origin main

# Остановка старых контейнеров
echo "⏹️ Остановка контейнеров..."
docker-compose down

# Сборка и запуск новых контейнеров
echo "🔨 Сборка и запуск контейнеров..."
docker-compose up -d --build

# Ожидание запуска бота
echo "⏳ Ожидание запуска сервисов..."
sleep 5

# Проверка статус контейнеров
echo "✅ Проверка статус контейнеров..."
docker-compose ps

# Проверка логов бота (последние 10 строк)
echo "📋 Последние логи бота:"
docker logs zumba_bot --tail 10

echo ""
echo "🎉 Деплой завершён!"
echo ""
echo "📊 Статус сервисов:"
docker ps --filter "name=zumba" --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"
echo ""
echo "🔍 Для просмотра логов используйте:"
echo "   docker logs -f zumba_bot"
echo "   docker logs -f zumba_web"
echo "   docker logs -f zumba_php"
