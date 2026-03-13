# 🚀 Инструкция по развёртыванию на сервере

**Дата:** 13 марта 2026 г.
**Коммит:** `d8adaee` - feat:admin-panel-fixes,image-updates,documentation

---

## 📋 Изменения

### 🔧 Исправление админ-панели
- Исправлены сессии PHP в Docker
- Детальное логирование ошибок
- Исправлена генерация CSRF токена
- Добавлена валидация данных
- Логирование в файл

### 🖼️ Обновление изображений
- Новое главное фото (3:4, вертикальное)
- Новое фото тренера
- Адаптивные версии (320, 480, 640, 800)
- WebP оптимизация

### 🎨 CSS улучшения
- Убран overlay с текстом при наведении
- Обновлены размеры изображений

### 📄 Документация
- LOCAL_SETUP.md
- ADMIN_DEPLOY.md
- ADMIN_FIX_REPORT.md
- IMAGE_*_REPORT.md

---

## 🚀 Развёртывание на сервере

### Шаг 1: Подключиться к серверу

```bash
ssh root@85.198.64.110
```

### Шаг 2: Перейти в директорию проекта

```bash
cd ~/zumba-site
```

### Шаг 3: Обновить код

```bash
git pull origin main
```

### Шаг 4: Пересобрать Docker контейнеры

```bash
# Пересобрать PHP контейнер (обновлён Dockerfile)
docker-compose build --no-cache php

# Перезапустить все сервисы
docker-compose up -d
```

### Шаг 5: Проверить статус контейнеров

```bash
docker-compose ps
```

**Ожидаемый результат:**
```
NAME        STATUS
zumba_bot   Up
zumba_db    Up
zumba_php   Up
zumba_web   Up
```

### Шаг 6: Проверить логи

```bash
# Логи PHP
docker-compose logs php

# Логи nginx
docker-compose logs web

# Логи админки
docker exec zumba_php tail -f /var/log/admin.log
```

---

## ✅ Проверка работы

### 1. Сайт
Откройте: **https://zumba-spb.ru**

Проверьте:
- ✅ Главное фото загрузилось
- ✅ Фото в секции "О тренере" загрузилось
- ✅ Нет overlay с текстом при наведении

### 2. Админ-панель
Откройте: **https://zumba-spb.ru/admin/**

Проверьте:
- ✅ Вход с паролем работает
- ✅ Сессии сохраняются (не разлогинивает)
- ✅ Сохранение данных работает
- ✅ Логи записываются

### 3. Мобильная версия
Проверьте с мобильного устройства:
- ✅ Адаптивность изображений
- ✅ Корректное отображение

---

## 🔍 Диагностика проблем

### Проблема: Сайт не открывается

```bash
# Проверить nginx
docker-compose logs web

# Проверить конфиг
docker exec zumba_web nginx -t

# Перезапустить nginx
docker-compose restart web
```

### Проблема: Админка не работает

```bash
# Проверить PHP
docker-compose logs php

# Проверить сессии
docker exec zumba_php ls -la /tmp/sessions/

# Проверить логи
docker exec zumba_php cat /var/log/admin.log

# Перезапустить PHP
docker-compose restart php
```

### Проблема: Изображения не загрузились

```bash
# Проверить файлы
docker exec zumba_web ls -la /var/www/html/hero-photo*

# Проверить права
docker exec zumba_web stat /var/www/html/hero-photo.png

# Перезапустить nginx
docker-compose restart web
```

### Проблема: Ошибка БД

```bash
# Проверить БД
docker-compose logs db

# Проверить подключение
docker exec zumba_db psql -U zumba_user -d zumba_db -c "SELECT 1"

# Перезапустить БД
docker-compose restart db
```

---

## 📊 Мониторинг

### Логи в реальном времени

```bash
# Все логи
docker-compose logs -f

# Только ошибки
docker-compose logs -f | grep -i error

# Логи админки
docker exec zumba_php tail -f /var/log/admin.log
```

### Проверка сессий

```bash
# Файлы сессий
docker exec zumba_php ls -la /tmp/sessions/

# Должны быть файлы sess_*
```

### Проверка логов

```bash
# Основной лог
docker exec zumba_php cat /var/log/admin.log

# Альтернативный лог
docker exec zumba_php cat /var/www/html/logs/admin.log
```

---

## 🔄 Откат изменений

Если что-то пошло не так:

### Вариант 1: Откат на предыдущий коммит

```bash
cd ~/zumba-site
git log --oneline -5
git revert HEAD
docker-compose restart php web
```

### Вариант 2: Восстановление файлов

```bash
# Восстановить админку
git checkout HEAD~1 admin/index.php
docker-compose restart php
```

---

## 📝 Чеклист развёртывания

- [ ] Код обновлён (`git pull`)
- [ ] Контейнеры пересобраны (`docker-compose build --no-cache php`)
- [ ] Контейнеры запущены (`docker-compose up -d`)
- [ ] Все контейнеры в статусе Up
- [ ] Сайт открывается
- [ ] Изображения загрузились
- [ ] Админка работает
- [ ] Сессии сохраняются
- [ ] Логи записываются
- [ ] Мобильная версия работает

---

## 📞 Контакты

- **GitHub:** https://github.com/vechniystudent86-alt/zumba-site-v2
- **Сайт:** https://zumba-spb.ru

---

**Версия:** 2.0.0
**Дата:** 2026-03-13
