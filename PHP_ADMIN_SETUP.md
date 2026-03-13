# 🔧 Инструкция по настройке PHP для админки

## Проблема
Ошибка **403 Forbidden** при доступе к `https://zumba-spb.ru/admin/`

## Причина
nginx не настроен для обработки PHP файлов

---

## Решение (на сервере)

### Шаг 1: Проверка PHP-FPM

```bash
# Проверить статус PHP-FPM
systemctl status php8.1-fpm

# Если не установлен - установить
apt update
apt install php8.1-fpm php8.1-curl php8.1-mbstring php8.1-pgsql

# Проверить версию
php -v
```

### Шаг 2: Проверка nginx конфига

```bash
# Открыть конфиг сайта
nano /etc/nginx/sites-available/zumba-site
```

**Добавить обработку PHP:**

```nginx
server {
    listen 443 ssl http2;
    server_name zumba-spb.ru www.zumba-spb.ru;

    ssl_certificate /etc/letsencrypt/live/zumba-spb.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/zumba-spb.ru/privkey.pem;

    root /var/www/zumba-site;
    index index.php index.html;

    # Обработка PHP
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Разрешить только определенные PHP файлы
        location ~ ^/admin/index\.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.1-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
        
        location ~ ^/send-form\.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/run/php/php8.1-fpm.sock;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
    
    # Запретить доступ к другим PHP файлам
    location ~ ^/(?!admin/index\.php|send-form\.php).*\.php$ {
        deny all;
        return 403;
    }

    location / {
        try_files $uri $uri/ =404;
    }
}
```

### Шаг 3: Проверка прав доступа

```bash
# Проверить права на файлы
ls -la /var/www/zumba-site/admin/

# Установить правильные права
chown -R www-data:www-data /var/www/zumba-site
chmod 755 /var/www/zumba-site
chmod 644 /var/www/zumba-site/admin/index.php
chmod 644 /var/www/zumba-site/config/admin_pass.txt
```

### Шаг 4: Проверка конфига PHP

```bash
# Проверить сокет PHP-FPM
ls -la /var/run/php/

# Должен быть файл php8.1-fpm.sock
```

### Шаг 5: Перезапуск служб

```bash
# Проверить конфиг nginx
nginx -t

# Перезапустить nginx
systemctl restart nginx

# Перезапустить PHP-FPM
systemctl restart php8.1-fpm

# Проверить статус
systemctl status nginx
systemctl status php8.1-fpm
```

### Шаг 6: Проверка логов

```bash
# Логи ошибок nginx
tail -20 /var/log/nginx/error.log

# Логи ошибок PHP-FPM
tail -20 /var/log/php8.1-fpm.log

# Логи доступа
tail -20 /var/log/nginx/access.log
```

---

## 📝 Тестирование

После настройки:

1. Открыть `https://zumba-spb.ru/admin/`
2. Должна появиться форма входа
3. Ввести пароль: `zumba2024`

---

## 🚀 Альтернативное решение

Если PHP настроить сложно, можно создать **простую HTML админку** с отправкой данных через JavaScript на backend (Python/FastAPI бот).

---

**Дата**: 2026-03-12  
**Версия**: 1.0
