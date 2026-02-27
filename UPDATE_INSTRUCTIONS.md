# 🔄 Инструкция по обновлению до версии 2.1.0

**Дата:** 27 февраля 2026 г.  
**Изменения:** CSRF защита + переименование index.html → index.php

---

## ⚠️ Важные изменения

### 1. index.html → index.php

Файл переименован для поддержки генерации CSRF токена на стороне сервера.

**Что нужно сделать на сервере:**

```bash
# Подключиться к серверу
ssh root@85.198.64.110

# Перейти в директорию сайта
cd /var/www/zumba-site

# Удалить старый index.html
rm index.html

# Обновить файлы из Git
git pull

# Проверить, что index.php существует
ls -la index.php
```

---

### 2. Обновление nginx конфигурации

nginx должен разрешать доступ к `index.php`:

```bash
# Скопировать обновщённый конфиг
cp ~/zumba-site/nginx-php.conf /etc/nginx/sites-available/zumba-site

# Проверить конфигурацию
nginx -t

# Перезапустить nginx
systemctl restart nginx
```

---

### 3. Проверка PHP

Убедитесь, что PHP-FPM запущен:

```bash
# Проверка статуса
systemctl status php8.1-fpm

# Если не запущен
systemctl start php8.1-fpm
systemctl enable php8.1-fpm
```

---

## 📋 Полный чек-лист обновления

### Локально (на вашем компьютере):

```cmd
# 1. Проверить изменения
git status

# 2. Закоммитить изменения
git add .
git commit -m "Update v2.1.0: CSRF protection, index.php, security fixes"

# 3. Отправить на сервер
git push
```

### На сервере (по SSH):

```bash
# 1. Обновить файлы
cd ~/zumba-site
git pull

# 2. Скопировать файлы в веб-директорию
cp -r ~/zumba-site/* /var/www/zumba-site/

# 3. Проверить права
chown -R www-data:www-data /var/www/zumba-site/
chmod 644 /var/www/zumba-site/index.php
chmod 644 /var/www/zumba-site/send-form.php

# 4. Создать директорию config (если не существует)
mkdir -p /var/www/zumba-site/config

# 5. Скопировать токены (если ещё не скопированы)
# Токены должны быть созданы локально и загружены на сервер
# config/telegram_token.txt
# config/telegram_chat_id.txt

# 6. Обновить nginx конфиг
cp ~/zumba-site/nginx-php.conf /etc/nginx/sites-available/zumba-site

# 7. Проверить nginx
nginx -t

# 8. Перезапустить nginx
systemctl restart nginx

# 9. Проверить логи
tail -20 /var/log/nginx/zumba-access.log
tail -20 /var/log/nginx/zumba-error.log
```

---

## ✅ Проверка работы

### 1. Проверка сайта

Откройте в браузере:
- https://zumba-spb.ru
- https://zumba-spb.ru/#contact

### 2. Проверка формы

1. Откройте консоль браузера (F12)
2. Перейдите на вкладку Network
3. Заполните и отправьте форму
4. Проверьте, что:
   - Запрос отправлен на `send-form.php`
   - В запросе есть параметр `csrf_token`
   - Ответ: `{"success":true,"message":"Заявка успешно отправлена",...}`

### 3. Проверка CSRF токена

В консоли браузера на странице сайта выполните:

```javascript
// Проверка наличия токена в форме
document.getElementById('csrf_token').value
// Должен вернуть строку из 64 символов (hex)
```

### 4. Проверка Telegram

После отправки формы проверьте, что сообщение пришло в Telegram бот.

---

## 🔧 Решение проблем

### Ошибка 403 Forbidden

**Проблема:** nginx блокирует доступ к index.php

**Решение:**
```bash
# Проверить nginx конфиг
cat /etc/nginx/sites-available/zumba-site | grep -A 10 "location ~* \\.php$"

# Убедиться, что index.php разрешён
# Должно быть: if ($uri != /send-form.php && $uri != /index.php)
```

### Ошибка 502 Bad Gateway

**Проблема:** PHP-FPM не запущен

**Решение:**
```bash
# Проверить статус
systemctl status php8.1-fpm

# Перезапустить
systemctl restart php8.1-fpm
```

### Ошибка CSRF токена

**Проблема:** Сессии не работают

**Решение:**
```bash
# Проверить права на директорию сессий
ls -la /var/lib/php/sessions/

# Исправить права
chown www-data:www-data /var/lib/php/sessions/
chmod 700 /var/lib/php/sessions/
```

### Форма не отправляется

**Проверка:**

```bash
# Логи PHP
tail -f /var/log/php/form_errors.log

# Логи nginx
tail -f /var/log/nginx/zumba-error.log

# Проверка доступа к Telegram API
curl https://api.telegram.org/bot8544616219:AAFiKfXks86HrqVbvuk77NvSARnBLlrHmaQ/getMe
```

---

## 📁 Новые файлы

| Файл | Назначение |
|------|------------|
| `index.php` | Главная страница с CSRF токеном |
| `config/telegram_token.txt` | Токен бота |
| `config/telegram_chat_id.txt` | Chat ID |
| `config/.gitignore` | Защита чувствительных файлов |
| `BUGFIX_REPORT.md` | Отчёт об исправлениях |
| `UPDATE_INSTRUCTIONS.md` | Этот файл |

---

## 🗑️ Удалённые файлы

| Файл | Причина |
|------|---------|
| `index.html` | Заменён на index.php |

---

## 📊 Сводка изменений v2.1.0

| Категория | Изменения |
|-----------|-----------|
| **Безопасность** | CSRF защита, токены в сессии |
| **Файлы** | index.html → index.php |
| **nginx** | Обновлён для поддержки index.php |
| **PHP** | Генерация токена при загрузке страницы |

---

## 📞 Контакты для поддержки

- **Email:** g1811kostukiv@yandex.ru
- **GitHub:** https://github.com/vechniystudent86-alt/zumba-site-v2

---

**Последнее обновление:** 27 февраля 2026 г.  
**Версия:** 2.1.0
