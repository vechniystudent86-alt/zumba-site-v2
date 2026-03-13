#!/bin/bash
# 🚀 Скрипт автоматического развёртывания на сервере
# Использование: ./deploy.sh

set -e

echo "========================================"
echo "🚀 Развёртывание Zumba Site на сервере"
echo "========================================"
echo ""

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Функция для печати статуса
print_status() {
    echo -e "${YELLOW}>>> $1${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Проверка Docker
print_status "Проверка Docker..."
if ! command -v docker &> /dev/null; then
    print_error "Docker не установлен!"
    exit 1
fi
print_success "Docker установлен"

if ! command -v docker-compose &> /dev/null; then
    print_error "docker-compose не установлен!"
    exit 1
fi
print_success "docker-compose установлен"

# Переход в директорию проекта
print_status "Переход в директорию проекта..."
cd ~/zumba-site || exit 1
print_success "Директория: $(pwd)"

# Git pull
print_status "Обновление кода из репозитория..."
git pull origin main
print_success "Код обновлён"

# Остановка контейнеров
print_status "Остановка контейнеров..."
docker-compose down
print_success "Контейнеры остановлены"

# Пересборка PHP контейнера
print_status "Пересборка PHP контейнера..."
docker-compose build --no-cache php
print_success "PHP контейнер пересобран"

# Запуск всех сервисов
print_status "Запуск сервисов..."
docker-compose up -d
print_success "Сервисы запущены"

# Пауза для запуска сервисов
print_status "Ожидание запуска сервисов (5 секунд)..."
sleep 5

# Проверка статуса
print_status "Проверка статуса контейнеров..."
docker-compose ps

# Проверка логов
print_status "Проверка логов (последние 10 строк)..."
docker-compose logs --tail=10

# Финальная проверка
print_status "Финальная проверка..."
if docker-compose ps | grep -q "Up"; then
    print_success "Все сервисы работают!"
else
    print_error "Некоторые сервисы не запустились!"
    exit 1
fi

echo ""
echo "========================================"
echo "✅ Развёртывание завершено успешно!"
echo "========================================"
echo ""
echo "🌐 Сайт: https://zumba-spb.ru"
echo "⚙️ Админка: https://zumba-spb.ru/admin/"
echo ""
echo "📝 Логи:"
echo "   docker-compose logs -f"
echo "   docker exec zumba_php tail -f /var/log/admin.log"
echo ""
