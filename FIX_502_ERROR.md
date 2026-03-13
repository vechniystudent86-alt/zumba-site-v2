# 🔧 Исправление ошибки 502 Bad Gateway на сервере

**Дата:** 13 марта 2026 г.
**Проблема:** nginx не может подключиться к PHP-FPM

---

## 🚀 Быстрое исправление

### 1. Подключиться к серверу

```bash
ssh root@85.198.64.110
```

### 2. Перейти в директорию проекта

```bash
cd ~/zumba-site
```

### 3. Проверить статус контейнеров

```bash
docker-compose ps
```

**Ожидаемый результат:**
```
NAME        STATUS
zumba_bot   Up
zumba_db    Up
zumba_php   Up    ← PHP должен быть Up
zumba_web   Up
```

### 4. Пересобрать и перезапустить контейнеры

```bash
# Остановить все контейнеры
docker-compose down

# Пересобрать PHP контейнер
docker-compose build --no-cache php

# Запустить все сервисы
docker-compose up -d

# Проверить статус
docker-compose ps
```

### 5. Проверить логи

```bash
# Логи PHP
docker-compose logs php

# Логи nginx
docker-compose logs web

# Проверить подключение nginx к PHP
docker exec zumba_web nginx -t
```

---

## 🔍 Диагностика

### Проверка 1: PHP контейнер запущен?

```bash
docker-compose ps php
```

Если не запущен:
```bash
docker-compose up -d php
```

### Проверка 2: PHP слушает порт 9000?

```bash
docker exec zumba_php netstat -tlnp | grep 9000
```

Или:
```bash
docker exec zumba_php ps aux | grep php-fpm
```

### Проверка 3: nginx видит PHP?

```bash
# Проверить сеть Docker
docker network ls

# Проверить контейнеры в сети
docker network inspect zumba-site_default
```

### Проверка 4: Разрешение имён

```bash
# Из nginx должен разрешаться хост 'php'
docker exec zumba_web ping -c 3 php
```

---

## 🛠️ Возможные проблемы и решения

### Проблема 1: PHP контейнер не запускается

**Решение:**
```bash
# Посмотреть логи
docker-compose logs php

# Пересобрать
docker-compose build --no-cache php
docker-compose up -d php
```

### Проблема 2: nginx не видит PHP

**Решение:**
```bash
# Пересоздать сеть
docker-compose down
docker network rm zumba-site_default
docker-compose up -d
```

### Проблема 3: Старый конфиг nginx

**Решение:**
```bash
# Обновить конфиг из git
cd ~/zumba-site
git pull

# Перезапустить nginx
docker-compose restart web
```

### Проблема 4: SSL сертификаты

**Решение:**
```bash
# Проверить сертификаты
ls -la /etc/letsencrypt/live/zumba-spb.ru/

# Обновить сертификаты
certbot renew

# Перезапустить nginx
docker-compose restart web
```

---

## 📊 Проверка работы

### 1. Проверить сайт

```bash
curl -I https://zumba-spb.ru
```

**Ожидаемый ответ:**
```
HTTP/2 200
content-type: text/html; charset=UTF-8
```

### 2. Проверить админку

```bash
curl -I https://zumba-spb.ru/admin/
```

**Ожидаемый ответ:**
```
HTTP/2 200
```

### 3. Проверить PHP

Создать тестовый файл:
```bash
echo '<?php phpinfo();' > /var/www/zumba-site/info.php
```

Открыть в браузере: `https://zumba-spb.ru/info.php`

Удалить после проверки:
```bash
rm /var/www/zumba-site/info.php
```

---

## 📝 Чеклист исправления

- [ ] SSH подключение работает
- [ ] Все контейнеры в статусе Up
- [ ] PHP контейнер слушает порт 9000
- [ ] nginx разрешает имя 'php'
- [ ] Логи чистые (нет ошибок)
- [ ] Сайт открывается (200 OK)
- [ ] Админка открывается
- [ ] Изображения загружаются

---

## 🔄 Если ничего не помогает

### Полный сброс:

```bash
cd ~/zumba-site

# Остановить и удалить всё
docker-compose down -v

# Пересобрать все контейнеры
docker-compose build --no-cache

# Запустить
docker-compose up -d

# Проверить
docker-compose ps
docker-compose logs -f
```

---

## 📞 Контакты

- **GitHub:** https://github.com/vechniystudent86-alt/zumba-site-v2
- **Сайт:** https://zumba-spb.ru

---

**Версия:** 1.0
**Дата:** 2026-03-13
