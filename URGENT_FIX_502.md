# 🚀 СРОЧНОЕ ИСПРАВЛЕНИЕ 502 ОШИБКИ

**Выполнить на сервере прямо сейчас!**

---

## ⚡ Быстрое исправление (5 минут)

### Шаг 1: Подключиться к серверу

```bash
ssh root@85.198.64.110
```

### Шаг 2: Перейти в проект

```bash
cd ~/zumba-site
```

### Шаг 3: Остановить контейнеры

```bash
docker-compose down
```

### Шаг 4: Пересобрать PHP

```bash
docker-compose build --no-cache php
```

### Шаг 5: Запустить всё

```bash
docker-compose up -d
```

### Шаг 6: Проверить

```bash
docker-compose ps
```

**Должно быть:**
```
NAME        STATUS
zumba_bot   Up
zumba_db    Up
zumba_php   Up    ← ВАЖНО!
zumba_web   Up
```

### Шаг 7: Проверить сайт

Откройте: **https://zumba-spb.ru**

---

## 🔍 Если не помогло

### Проверить логи PHP:

```bash
docker-compose logs php
```

### Проверить логи nginx:

```bash
docker-compose logs web
```

### Перезапустить nginx:

```bash
docker-compose restart web
```

### Проверить сеть Docker:

```bash
docker network ls
docker network inspect zumba-site_default
```

---

## 📞 После исправления

Проверьте:
1. ✅ Сайт открывается: https://zumba-spb.ru
2. ✅ Админка работает: https://zumba-spb.ru/admin/
3. ✅ Изображения загружаются

---

**Время выполнения:** 3-5 минут
**Сложность:** ⭐ (очень просто)
