#!/bin/sh
set -e

# Даем права веб-серверу на папки, куда админка пишет данные
mkdir -p /var/www/html/data /var/www/html/config
chown -R www-data:www-data /var/www/html/data /var/www/html/config

# Убеждаемся, что директории для сессий и логов существуют и доступны
mkdir -p /tmp/sessions
chmod 777 /tmp/sessions
mkdir -p /var/log
chmod 777 /var/log
mkdir -p /var/www/html/logs
chown www-data:www-data /var/www/html/logs

# Запускаем основной процесс
exec "$@"
