# 🚀 Инструкция по деплою интеграции с CRM

## 📦 Что изменено

1. **send-form.php** — полностью переписан:
   - ✅ Отправка заявок в CRM API (`/api/leads`)
   - ✅ Автоматическое создание клиента при первом обращении
   - ✅ Уведомление в Telegram (дублирование)
   - ✅ Серверная валидация данных
   - ✅ Rate limiting (защита от спама)

2. **Конфиг файлы** (папка `config/`):
   - `crm_url.txt` — URL CRM API
   - `telegram_token.txt` — токен бота
   - `telegram_chat_id.txt` — ваш Chat ID

---

## 📤 Деплой на сервер

### 1. Отправьте файлы на сервер

```bash
# Подключитесь к серверу
ssh root@85.198.64.110

# Перейдите в директорию сайта
cd ~/zumba-site

# Загрузите новые файлы (или обновите через git)
git pull

# Скопируйте файлы в веб-директорию
cp send-form.php /var/www/zumba-site/send-form.php
cp -r config/ /var/www/zumba-site/config/

# Проверьте права
chown www-data:www-data /var/www/zumba-site/send-form.php
chown -R www-data:www-data /var/www/zumba-site/config/
chmod 644 /var/www/zumba-site/send-form.php
chmod 644 /var/www/zumba-site/config/*.txt
```

### 2. Проверьте PHP

```bash
php -l /var/www/zumba-site/send-form.php
```

Должно быть: `No syntax errors detected`

### 3. Убедитесь что CRM запущен

```bash
# Проверка процесса
ps aux | grep uvicorn

# Или запустите (если не запущен)
cd ~/crm-backend
source venv/bin/activate
uvicorn app.main:app --host 0.0.0.0 --port 8000
```

Для production используйте systemd или docker:

```bash
# Если через systemd
systemctl status crm-backend

# Если через docker
docker ps | grep crm
```

---

## 🧪 Тестирование

### 1. Тест CRM API (через curl)

```bash
curl -X POST http://localhost:8000/api/leads \
  -H "Content-Type: application/json" \
  -d '{"name":"Тест Тестов","phone":"+79991234567","program":"classic","message":"Тест","source":"website"}'
```

**Ожидаемый ответ:**
```json
{
  "id": 1,
  "name": "Тест Тестов",
  "phone": "+79991234567",
  "program": "classic",
  "message": "Тест",
  "source": "website",
  "status": "new",
  "client_id": 1,
  "created_at": "2026-02-28T..."
}
```

### 2. Тест формы на сайте

1. Откройте https://zumba-spb.ru
2. Заполните форму заявки:
   - Имя: `Тест`
   - Телефон: `+79991234567`
   - Программа: `Zumba Classic`
3. Нажмите "Отправить"

**Ожидаемый результат:**
- ✅ Сообщение "Заявка успешно отправлена!"
- ✅ Уведомление в Telegram
- ✅ Заявка в CRM (через Swagger UI: http://85.198.64.110:8000/docs)

### 3. Проверка логов

```bash
# Логи PHP формы
cat /var/log/php/form_errors.log | tail -20

# Логи nginx
tail -20 /var/log/nginx/zumba-error.log

# Логи CRM (если через docker)
docker logs crm-backend
```

---

## 🔧 Настройка CRM на продакшен

### 1. Обновите .env в CRM

```bash
cd ~/crm-backend
nano .env
```

Добавьте/обновите:

```env
# Telegram Bot (опционально для уведомлений)
TELEGRAM_BOT_TOKEN=8544616219:AAFiKfXks86HrqVbvuk77NvSARnBLlrHmaQ
TELEGRAM_WEBHOOK_SECRET=your-secret
```

### 2. CORS (если сайт на другом домене)

В `crm-backend/app/main.py`:

```python
app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://zumba-spb.ru"],  # Ваш домен
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)
```

### 3. Nginx для CRM (если нужно)

```bash
cat > /etc/nginx/sites-available/crm << 'EOF'
server {
    listen 80;
    server_name crm.zumba-spb.ru;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

ln -sf /etc/nginx/sites-available/crm /etc/nginx/sites-enabled/crm
nginx -t
systemctl reload nginx
```

---

## 📊 Просмотр заявок в CRM

### Swagger UI

1. Откройте http://85.198.64.110:8000/docs
2. Авторизуйтесь (`POST /api/auth/login`)
3. Используйте endpoints:
   - `GET /api/leads/` — список заявок
   - `GET /api/leads/stats/summary` — статистика
   - `PATCH /api/leads/{id}` — обновить статус

### Статусы заявок

- `new` — новая заявка
- `contacted` — связались с клиентом
- `converted` — клиент купил абонемент
- `rejected` — отказ/недозвон

---

## ❓ Troubleshooting

### Заявки не приходят в CRM

1. Проверьте доступность CRM:
   ```bash
   curl http://localhost:8000/docs
   ```

2. Проверьте логи:
   ```bash
   cat /var/log/php/form_errors.log | tail -20
   ```

3. Проверьте URL в конфиге:
   ```bash
   cat /var/www/zumba-site/config/crm_url.txt
   ```

### Ошибка "Connection refused"

CRM не запущен. Запустите:

```bash
cd ~/crm-backend
source venv/bin/activate
uvicorn app.main:app --host 0.0.0.0 --port 8000
```

### Telegram не отправляет

1. Проверьте токен:
   ```bash
   curl https://api.telegram.org/bot8544616219:AAFiKfXks86HrqVbvuk77NvSARnBLlrHmaQ/getMe
   ```

2. Проверьте chat_id (должен быть числом)

---

## 📞 Поддержка

При проблемах:
1. Проверьте логи PHP и CRM
2. Протестируйте API через Swagger
3. Убедитесь что CRM запущен и доступен

---

**Версия:** 1.0  
**Дата:** 28 февраля 2026
