# 🚀 Локальный запуск zumba-site

**Обновлено:** 13 марта 2026 г.

---

## 📋 Быстрый старт

### 1. Проверка требований

Убедитесь, что установлены:
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) для Windows
- Git (опционально, для работы с кодом)

### 2. Запуск проекта

```bash
# Перейдите в директорию проекта
cd C:\Users\lord0\OneDrive\Desktop\Python\Sanika\zumba-site

# Запустите все контейнеры
docker-compose up -d --build

# Проверьте статус
docker-compose ps
```

### 3. Откройте сайт

- **Сайт:** http://localhost:8080
- **Админ-панель:** http://localhost:8080/admin/
- **API бота:** http://localhost:8080/api/

---

## 🔧 Конфигурация

### Файл .env

Проект использует файл `.env` для конфигурации. Если его нет, создайте из примера:

```bash
copy .env.example .env
```

### Конфигурационные файлы

В папке `config/` должны быть следующие файлы:

| Файл | Описание | Значение по умолчанию |
|------|----------|----------------------|
| `telegram_token.txt` | Токен Telegram бота | (требуется для бота) |
| `telegram_chat_id.txt` | Chat ID для уведомлений | (требуется для бота) |
| `admin_pass.txt` | Пароль для админ-панели | `zumba2024` |
| `crm_url.txt` | URL CRM для лидов | (опционально) |

Создать файлы можно так:

```bash
# Пароль для админки
echo zumba2024 > config\admin_pass.txt

# Telegram токен (получить у @BotFather)
echo 1234567890:ABCdefGHIjklMNOpqrsTUVwxyz > config\telegram_token.txt

# Chat ID (получить у @userinfobot)
echo 123456789 > config\telegram_chat_id.txt
```

---

## 📦 Контейнеры

| Контейнер | Порт | Описание |
|-----------|------|----------|
| `zumba_web` | 8080 | Nginx веб-сервер |
| `zumba_php` | 9000 | PHP-FPM для админки |
| `zumba_db` | 5432 | PostgreSQL база данных |
| `zumba_bot` | 8000 | Python бот (внутренний) |

---

## 🛠️ Полезные команды

### Просмотр логов

```bash
# Все логи
docker-compose logs -f

# Логи конкретного сервиса
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f bot
docker-compose logs -f db

# Логи админки (внутри контейнера)
docker exec -it zumba_php tail -f /var/log/admin.log
```

### Перезапуск контейнеров

```bash
# Перезапустить всё
docker-compose restart

# Перезапустить конкретный сервис
docker-compose restart php
docker-compose restart nginx
```

### Пересборка контейнеров

```bash
# Пересобрать и перезапустить
docker-compose up -d --build

# Пересобрать конкретный сервис
docker-compose build --no-cache php
docker-compose up -d php
```

### Остановка проекта

```bash
# Остановить все контейнеры
docker-compose down

# Остановить и удалить volumes (база данных будет очищена!)
docker-compose down -v
```

---

## 🧪 Тестирование админ-панели

### 1. Откройте админку

```
http://localhost:8080/admin/
```

### 2. Войдите

**Пароль по умолчанию:** `zumba2024`

### 3. Проверьте функциональность

- [ ] Изменение цен на абонементы
- [ ] Редактирование расписания
- [ ] Изменение контактов
- [ ] Смена пароля
- [ ] Проверка логов

---

## 🔍 Диагностика проблем

### Контейнер не запускается

```bash
# Проверить логи
docker-compose logs <сервис>

# Проверить статус
docker-compose ps

# Пересобрать
docker-compose up -d --build
```

### Ошибка 403/404/500

```bash
# Проверить логи nginx
docker-compose logs web

# Проверить логи PHP
docker-compose logs php

# Проверить права на файлы
docker exec -it zumba_php ls -la /var/www/html/admin/
```

### Бот не работает

```bash
# Проверить логи бота
docker-compose logs bot

# Проверить подключение к БД
docker exec -it zumba_bot python -c "import asyncio; from database import get_db; print('OK')"

# Проверить переменные окружения
docker exec -it zumba_bot env | grep DATABASE
```

### Ошибка подключения к БД

```bash
# Проверить статус БД
docker-compose ps db

# Проверить логи БД
docker-compose logs db

# Проверить подключение
docker exec -it zumba_db psql -U zumba_user -d zumba_db -c "SELECT 1"
```

---

## 📝 Примечания

### Локальная конфигурация nginx

Для локальной разработки используется упрощённая конфигурация nginx без SSL:
- Порт: **8080** (вместо 80/443)
- Без HTTPS (SSL сертификаты не требуются)
- Конфигурация: `docker/nginx/local.conf`

### Production конфигурация

Для продакшена используется:
- Порт: 80/443
- HTTPS с Let's Encrypt
- Конфигурация: `docker/nginx/default.conf`

### Сессии и логи

- **Сессии PHP:** `/tmp/sessions` внутри контейнера
- **Логи админки:** `/var/log/admin.log` или `logs/admin.log`
- **Логи nginx:** внутри контейнера, доступны через `docker-compose logs web`

---

## 📞 Контакты

- **Сайт:** https://zumba-spb.ru
- **Владелец:** g1811kostukiviv@yandex.ru

---

**Версия:** 2.0.0
**Последнее обновление:** 2026-03-13
