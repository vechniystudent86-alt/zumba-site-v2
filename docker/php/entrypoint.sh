#!/bin/sh
set -e

# Даем права веб-серверу на папки, куда админка пишет данные
mkdir -p /var/www/html/data /var/www/html/config
chown -R www-data:www-data /var/www/html/data /var/www/html/config

# Запускаем основной процесс
exec "$@"
