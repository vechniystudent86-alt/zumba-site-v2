# 🚀 Инструкция по деплою на сервер

## ✅ Изменения закоммичены и отправлены на сервер

Все изменения уже в репозитории на GitHub:
- https://github.com/vechniystudent86-alt/zumba-site-v2

---

## 📋 Шаги для применения на сервере

### 1. Подключиться к серверу по SSH:
```bash
ssh root@ваш-server.com
```

### 2. Перейти в директорию проекта:
```bash
cd /root/zumba-site
```

### 3. Запустить скрипт деплоя:
```bash
chmod +x deploy.sh
./deploy.sh
```

**ИЛИ вручную:**
```bash
# Остановить контейнеры
docker-compose down

# Обновить код
git pull origin main

# Запустить контейнеры
docker-compose up -d --build
```

### 4. Применить миграцию БД (если таблица settings не создалась автоматически):
```bash
docker exec -i zumba_db psql -U zumba_user -d zumba_db < migrations/001_create_settings_table.sql
```

### 5. Проверить работу:
```bash
# Проверить логи бота
docker logs -f zumba_bot

# Проверить таблицу settings
docker exec -it zumba_db psql -U zumba_user -d zumba_db -c "SELECT * FROM settings;"
```

---

## 🧪 Проверка функционала

### 1. Telegram-бот:
1. Откройте бота в Telegram
2. Нажмите "👩‍🏫 Панель тренера"
3. Нажмите "✏️ Редактировать"
4. Попробуйте изменить цену или контакт

### 2. Веб-админка:
1. Откройте https://ваш-домен/admin
2. Войдите с паролем
3. Измените цены/контакты
4. Нажмите "Сохранить"
5. Проверьте, что появилось сообщение "✅ Настройки синхронизированы с ботом!"

---

## 🔧 Если что-то пошло не так

### Бот не запускается:
```bash
# Проверить логи
docker logs zumba_bot

# Перезапустить
docker-compose restart bot
```

### Ошибка БД:
```bash
# Применить миграцию вручную
docker exec -it zumba_db psql -U zumba_user -d zumba_db -f /docker-entrypoint-initdb.d/001_create_settings_table.sql
```

### Проверить API бота:
```bash
curl http://localhost:8000/api/settings
```

---

## 📞 Контакты для связи

При возникновении проблем обратитесь к разработчику.
