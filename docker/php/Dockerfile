FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

# Устанавливаем теневые пароли и меняем UID www-data, чтобы он совпадал с хостом
RUN apk add --no-cache shadow \
    && usermod -u 1000 www-data \
    && groupmod -g 1000 www-data

COPY docker/php/entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]
