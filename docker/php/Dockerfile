FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

# Устанавливаем зависимости и драйверы
RUN apk add --no-cache shadow \
    && apk add --no-cache postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && usermod -u 1000 www-data \
    && groupmod -g 1000 www-data \
    # Создаем директорию для PHP сессий
    && mkdir -p /tmp/sessions \
    && chmod 777 /tmp/sessions \
    # Создаем директорию для логов
    && mkdir -p /var/log \
    && chmod 777 /var/log

COPY docker/php/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
