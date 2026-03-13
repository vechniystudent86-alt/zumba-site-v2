# 🚨 ВОССТАНОВЛЕНИЕ СЕРВЕРА ZUMBA-SPB.RU

**Сайт недоступен полностью!**

---

## 📞 СРОЧНО ВЫПОЛНИТЬ НА СЕРВЕРЕ

### Шаг 1: Подключиться по SSH

```bash
ssh root@85.198.64.110
```

**Если не подключается:**
- Проверьте SSH ключи
- Используйте пароль root
- Через панель хостинга (если есть)

---

### Шаг 2: Проверить Docker

```bash
docker ps -a
```

**Если Docker не работает:**
```bash
systemctl status docker
systemctl restart docker
```

---

### Шаг 3: Перейти в проект

```bash
cd ~/zumba-site
```

---

### Шаг 4: Полная пересборка

```bash
# Остановить всё
docker-compose down

# Удалить старые образы
docker-compose build --no-cache

# Запустить
docker-compose up -d

# Проверить
docker-compose ps
```

---

### Шаг 5: Проверить nginx

```bash
# Логи nginx
docker-compose logs web

# Проверить конфиг
docker exec zumba_web nginx -t

# Перезапустить nginx
docker-compose restart web
```

---

### Шаг 6: Проверить порты

```bash
# Какие порты слушает nginx
docker exec zumba_web netstat -tlnp

# Должно быть: 0.0.0.0:80 и 0.0.0.0:443
```

---

### Шаг 7: Проверить брандмауэр

```bash
# Для Ubuntu/Debian
ufw status

# Если активен - открыть порты
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 22/tcp
```

---

### Шаг 8: Проверить SSL сертификаты

```bash
# Проверить наличие
ls -la /etc/letsencrypt/live/zumba-spb.ru/

# Если нет - обновить
certbot renew --force-renewal
```

---

## 🔍 Диагностика

### Сервер не отвечает по SSH

**Решение:**
1. Перезагрузить сервер через панель хостинга
2. Проверить сеть
3. Обратиться в поддержку хостинга

### Docker не запускается

**Решение:**
```bash
systemctl status docker
journalctl -u docker -n 50
systemctl restart docker
```

### Контейнеры не запускаются

**Решение:**
```bash
# Посмотреть логи
docker-compose logs

# Проверить образы
docker images

# Очистить место
docker system prune -a --volumes
```

### nginx не запускается

**Решение:**
```bash
# Проверить конфиг
docker exec zumba_web nginx -t

# Логи
docker-compose logs web

# Пересоздать контейнер
docker-compose rm -f web
docker-compose up -d web
```

---

## ✅ Проверка после восстановления

### 1. Проверить статус

```bash
docker-compose ps
```

**Все должны быть Up:**
```
zumba_bot   Up
zumba_db    Up
zumba_php   Up
zumba_web   Up
```

### 2. Проверить порты

```bash
netstat -tlnp | grep nginx
```

**Должно быть:**
```
0.0.0.0:80
0.0.0.0:443
```

### 3. Проверить сайт

```bash
curl -I https://zumba-spb.ru
```

**Ответ:**
```
HTTP/2 200
```

### 4. Проверить админку

```bash
curl -I https://zumba-spb.ru/admin/
```

**Ответ:**
```
HTTP/2 200
```

---

## 📊 Команды для мониторинга

```bash
# Логи в реальном времени
docker-compose logs -f

# Использование ресурсов
docker stats

# Место на диске
df -h

# Оперативная память
free -h

# Процессы
top
```

---

## 🔄 Если ничего не помогает

### Полная переустановка:

```bash
cd ~/zumba-site

# Удалить всё
docker-compose down -v
docker system prune -a --volumes

# Очистить кэш
apt clean

# Обновить пакеты
apt update && apt upgrade -y

# Переустановить Docker (если нужно)
curl -fsSL https://get.docker.com | sh

# Пересобрать проект
git pull
docker-compose build --no-cache
docker-compose up -d
```

---

## 📞 Экстренная помощь

### Хостинг-провайдер:
- Проверить статус сервера
- Проверить сеть
- Проверить блокировки

### Резервная копия:
```bash
# Сделать бэкап БД
docker exec zumba_db pg_dump -U zumba_user zumba_db > backup.sql

# Скопировать файлы
tar -czf backup-files.tar.gz /var/www/zumba-site/
```

---

**Время восстановления:** 10-15 минут
**Сложность:** ⭐⭐ (средняя)

---

## ✅ Чеклист

- [ ] SSH подключение работает
- [ ] Docker запущен
- [ ] Все контейнеры Up
- [ ] Порты 80 и 443 открыты
- [ ] nginx работает
- [ ] SSL сертификаты действительны
- [ ] Сайт открывается
- [ ] Админка работает

---

**Дата:** 2026-03-13
**Версия:** 2.0
