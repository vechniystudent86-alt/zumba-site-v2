# 🔧 Инструкция по развёртыванию и настройке

## 📋 Изменения в версии 2.0

### ✅ Исправленные уязвимости

1. **Безопасность форм**
   - ❌ Удалены API ключи Telegram из JavaScript
   - ✅ Создан серверный PHP-обработчик `send-form.php`
   - ✅ Добавлена серверная валидация данных
   - ✅ Rate limiting (5 запросов в 5 минут с одного IP)
   - ✅ Защита от CSRF и спама

2. **Минификация**
   - ✅ Автоматическая минификация CSS/JS
   - ✅ Build-скрипты для Windows (PowerShell)
   - ✅ Поддержка режима наблюдения (watch)

---

## 🚀 Быстрый старт

### 1. Установка зависимостей (локально)

```bash
cd C:\Users\lord0\OneDrive\Desktop\Python\Sanika\zumba-site

# Установите Node.js зависимости
npm install
```

### 2. Минификация файлов

```bash
# Однократная сборка
npm run build

# Или режим наблюдения (авто-сборка при изменениях)
npm run watch
```

### 3. Деплой на сервер

```bash
# Отправить изменения в Git
git add .
git commit -m "Update: security fixes and build system"
git push

# На сервере (SSH)
ssh root@85.198.64.110

# Обновить файлы
cd ~/zumba-site
git pull

# Скопировать файлы в веб-директорию
cp -r ~/zumba-site/* /var/www/zumba-site/

# Проверить права
chown -R www-data:www-data /var/www/zumba-site
chmod 644 /var/www/zumba-site/*.php
```

---

## 🔐 Настройка сервера (nginx + PHP)

### Шаг 1: Установите PHP-FPM (если не установлен)

```bash
# Для Ubuntu/Debian
apt update
apt install php8.1-fpm php8.1-curl php8.1-mbstring

# Проверка версии
php -v

# Статус службы
systemctl status php8.1-fpm
```

### Шаг 2: Настройте nginx

```bash
# Скопируйте конфиг
cp ~/zumba-site/nginx-php.conf /etc/nginx/sites-available/zumba-site

# Отредактируйте путь к PHP сокету (если нужно)
nano /etc/nginx/sites-available/zumba-site

# Создайте симлинк
ln -sf /etc/nginx/sites-available/zumba-site /etc/nginx/sites-enabled/

# Удалите дефолтный сайт
rm -f /etc/nginx/sites-enabled/default

# Добавьте rate limiting в nginx.conf
# Откройте файл:
nano /etc/nginx/nginx.conf

# Добавьте в http блок:
limit_req_zone $binary_remote_addr zone=form_limit:10m rate=5r/m;

# Проверьте конфигурацию
nginx -t

# Перезапустите nginx
systemctl restart nginx
systemctl status nginx
```

### Шаг 3: Проверка PHP

```bash
# Создайте тестовый файл
echo '<?php phpinfo();' > /var/www/zumba-site/info.php

# Откройте в браузере: https://zumba-spb.ru/info.php

# Удалите после проверки
rm /var/www/zumba-site/info.php
```

---

## 📁 Структура файлов

```
zumba-site/
├── index.html              # Главная страница
├── styles.css              # Основные стили (исходник)
├── styles.min.css          # Минифицированные стили
├── responsive.css          # Адаптивные стили (исходник)
├── responsive.min.css      # Минифицированные адаптивные стили
├── script.js               # JavaScript (исходник)
├── script.min.js           # Минифицированный JavaScript
├── send-form.php           # 🆕 Обработчик форм (НОВЫЙ!)
├── package.json            # 🆕 NPM зависимости (НОВЫЙ!)
├── build.ps1               # 🆕 Build скрипт PowerShell (НОВЫЙ!)
├── nginx-php.conf          # 🆕 Конфиг nginx с PHP (НОВЫЙ!)
├── .gitignore              # Игнорируемые файлы
└── DEPLOYMENT.md           # Этот файл
```

---

## 🛡️ Безопасность

### Что защищено:

| Угроза | Защита |
|--------|--------|
| XSS атаки | Серверная валидация, экранирование |
| CSRF | Проверка метода POST, заголовки |
| Спам | Rate limiting (5 запросов/5 мин) |
| DDoS форм | IP-based rate limiting в nginx |
| Доступ к PHP | Запрет всех PHP кроме send-form.php |
| Скачивание бэкапов | Блокировка .bak, .sql, .config |

### Рекомендации:

1. **Периодически меняйте Telegram токен**
   - Через @BotFather в Telegram

2. **Мониторьте логи**
   ```bash
   # Просмотр ошибок PHP
   tail -f /var/log/php8.1-fpm.log
   
   # Просмотр ошибок nginx
   tail -f /var/log/nginx/zumba-error.log
   
   # Отслеживание попыток атак
   grep "403" /var/log/nginx/zumba-access.log
   ```

3. **Настройте firewall**
   ```bash
   ufw allow 80/tcp
   ufw allow 443/tcp
   ufw allow 22/tcp
   ufw enable
   ```

---

## 🧪 Тестирование

### Проверка формы:

1. Откройте https://zumba-spb.ru
2. Заполните форму заявки
3. Проверьте получение в Telegram

### Проверка валидации:

```bash
# Пустое имя
curl -X POST https://zumba-spb.ru/send-form.php \
  -d "name=&phone=+79991234567&program=classic"

# Ожидаемый ответ: {"success":false,"error":"Введите корректное имя..."}

# Невалидный телефон
curl -X POST https://zumba-spb.ru/send-form.php \
  -d "name=Тест&phone=123&program=classic"

# Ожидаемый ответ: {"success":false,"error":"Введите корректный номер..."}

# Rate limiting (5 запросов подряд)
for i in {1..6}; do
  curl -X POST https://zumba-spb.ru/send-form.php \
    -d "name=Тест&phone=+79991234567&program=classic"
  echo ""
done
```

### Проверка минификации:

```bash
# Запустить сборку
npm run build

# Проверить размеры
ls -lh *.css *.js *.min.css *.min.js

# Ожидаемая экономия: ~50-70%
```

---

## 🔧 Решение проблем

### Ошибка 403 Forbidden

```bash
# Проверьте права на файл
ls -la /var/www/zumba-site/send-form.php

# Исправьте права
chmod 644 /var/www/zumba-site/send-form.php
chown www-data:www-data /var/www/zumba-site/send-form.php
```

### Ошибка 502 Bad Gateway

```bash
# Проверьте, запущен ли PHP-FPM
systemctl status php8.1-fpm

# Перезапустите
systemctl restart php8.1-fpm

# Проверьте путь к сокету
ls -la /var/run/php/
```

### Форма не отправляется

```bash
# Проверьте логи PHP
tail -f /var/log/php8.1-fpm.log

# Проверьте логи nginx
tail -f /var/log/nginx/zumba-error.log

# Проверьте доступность Telegram API
curl https://api.telegram.org/bot8544616219:AAFiKfXks86HrqVbvuk77NvSARnBLlrHmaQ/getMe
```

### Минификация не работает

```bash
# Проверьте установку Node.js
node -v
npm -v

# Переустановите зависимости
rm -rf node_modules package-lock.json
npm install

# Проверьте пути к файлам
npm run build --verbose
```

---

## 📊 Мониторинг

### Настройте уведомления об ошибках:

```bash
# Добавьте в /etc/php/8.1/fpm/php.ini
error_log = /var/log/php8.1-fpm.log
log_errors = On
display_errors = Off
```

### Логи формы в send-form.php:

```php
// Добавьте после строки с получением данных
file_put_contents('/var/log/form_submissions.log', 
    date('Y-m-d H:i:s') . " - $name - $phone\n", 
    FILE_APPEND);
```

---

## 📞 Контакты для поддержки

- **Владелец:** g1811kostukiv@yandex.ru
- **GitHub:** https://github.com/vechniystudent86-alt/zumba-site-v2
- **Сайт:** https://zumba-spb.ru

---

**Последнее обновление:** 26 февраля 2026 г.
**Версия:** 2.0.0
