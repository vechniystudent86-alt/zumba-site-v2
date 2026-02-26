# Zumba Сайт - История Проекта

## 📋 Информация о проекте

**Название:** Zumba Юго-Западная | Александра Мельникова
**Домен:** zumba-spb.ru
**Сервер:** 85.198.64.110
**GitHub:** https://github.com/vechniystudent86-alt/zumba-site-v2

**Тренер:** Александра Мельникова (женщина)
**Локация:** Санкт-Петербург, ул. Маршала Захарова 20Д (клуб "Радуга")

---

## 🌐 Социальные сети

| Платформа | Ссылка |
|-----------|--------|
| Telegram | https://t.me/ZumbaYugozapadSPB |
| ВКонтакте | https://vk.ru/radugaclub20 |

---

## 📁 Структура проекта

```
zumba-site/
├── index.html              # Главная страница
├── styles.css              # Основные стили (исходник)
├── styles.min.css          # Минифицированные стили
├── responsive.css          # Адаптивные стили (исходник)
├── responsive.min.css      # Минифицированные адаптивные стили
├── script.js               # JavaScript (исходник)
├── script.min.js           # Минифицированный JavaScript
├── send-form.php           # 🔒 Обработчик форм (серверный)
├── package.json            # NPM зависимости
├── build.ps1               # Build скрипт PowerShell
├── deploy.bat              # Скрипт деплоя (локально)
├── deploy-remote.bat       # Скрипт деплоя (на сервер)
├── nginx-php.conf          # Конфиг nginx с PHP
├── .gitignore              # Игнорируемые файлы
├── DEPLOYMENT.md           # 🔒 Инструкция по развёртыванию
├── PROJECT_HISTORY.md      # Этот файл
└── Скрины/                 # Скриншоты
```

---

## 🎨 Дизайн и особенности

### Цветовая палитра
- **Primary:** #FF2D75 (розовый)
- **Secondary:** #FFB800 (золотой)
- **Accent:** #00D9FF (голубой)
- **Background:** #0D0D0D (тёмный)

### Особенности
- ✅ Современный дизайн с градиентами
- ✅ Анимация сердечек при движении мыши
- ✅ Кастомный курсор (отключён)
- ✅ 3D tilt-эффект на карточках
- ✅ Анимация счётчиков статистики
- ✅ Плавный скролл
- ✅ Параллакс эффект
- ✅ Адаптивная вёрстка (мобильные + десктоп)

### Шрифты
- **Display:** Bebas Neue
- **Body:** Montserrat

---

## 📊 Яндекс.Метрика

**Счётчик:** 106970869  
**Настройки:**
- Вебвизор: включён
- Карта кликов: включена
- Трекинг ссылок: включён
- Точный показатель отказов: включён

**Код установлен в `<head>` index.html**

---

## 🚀 Деплой и развёртывание

### GitHub
```bash
cd C:\Users\lord0\OneDrive\Desktop\Python\Sanika\zumba-site
git add .
git commit -m "Описание изменений"
git push
```

### Сервер (обновление)
```bash
ssh root@85.198.64.110
cd ~/zumba-site
git pull
cp -r ~/zumba-site/* /var/www/zumba-site/
```

### Nginx конфиг
Расположение: `/etc/nginx/sites-available/zumba-site`

```nginx
server {
    listen 80;
    server_name zumba-spb.ru www.zumba-spb.ru;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name zumba-spb.ru www.zumba-spb.ru;
    
    ssl_certificate /etc/letsencrypt/live/zumba-spb.ru/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/zumba-spb.ru/privkey.pem;
    
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_prefer_server_ciphers on;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_session_cache shared:SSL:10m;
    ssl_session_timeout 10m;
    
    root /var/www/zumba-site;
    index index.html;
    
    location / {
        try_files $uri $uri/ =404;
    }
}
```

---

## 🔒 SSL сертификат

**Статус:** ✅ Настроен и работает  
**Сертификат:** Let's Encrypt  
**Домены:** zumba-spb.ru, www.zumba-spb.ru  
**Истекает:** 2026-05-24  
**Автопродление:** настроено

**Конфиг nginx:** `/etc/nginx/sites-available/zumba-site`

**Команды:**
```bash
# Получение сертификата
certbot --nginx -d zumba-spb.ru -d www.zumba-spb.ru --email g1811kostukiv@yandex.ru --agree-tos --force-renewal

# Проверка продления
certbot renew --dry-run

# Просмотр сертификатов
certbot certificates
```

---

## 📝 DNS записи

**Текущие DNS (в панели Beget):**
```
A-запись: @ → 85.198.64.110 ✅
A-запись: www → 85.198.64.110 ⚠️ (было 5.101.152.161)
MX: 10 mx1.beget.com.
MX: 20 mx2.beget.com.
TXT: v=spf1 redirect=beget.com
```

**⚠️ Важно:** DNS для `www.zumba-spb.ru` может ещё обновляться (2-24 часа)

---

## ✅ Выполненные задачи

- [x] Создан одностраничный сайт для Zumba тренера
- [x] Современный дизайн с анимациями
- [x] Анимация сердечек при движении мыши
- [x] Заполнены все секции (О тренере, Программы, Отзывы, Контакты)
- [x] Добавлены ссылки на Telegram и VK
- [x] Обновлены контакты для Санкт-Петербурга
- [x] Добавлена Яндекс.Метрика (счётчик 106970869)
- [x] Настроен GitHub репозиторий
- [x] Развёрнуто на сервере (85.198.64.110)
- [x] Настроен nginx
- [x] Получен SSL сертификат
- [x] Настроен HTTPS для zumba-spb.ru и www.zumba-spb.ru
- [x] DNS обновлён и направлен на сервер

---

## ⏳ Текущие задачи / Проблемы

### DNS
- ✅ DNS обновлён
- ✅ Оба домена работают (zumba-spb.ru и www.zumba-spb.ru)

### nginx
- ✅ SSL сертификат установлен
- ✅ HTTPS работает для обоих доменов

---

## 🔧 Будущие улучшения

### Контент
- [ ] Добавить реальное фото Александры
- [ ] Добавить фото с тренировок
- [ ] Видео с занятий (YouTube/Vimeo)
- [ ] Галерея фотографий

### Функционал
- [ ] Форма заявки с отправкой на email/Telegram
- [ ] Онлайн-расписание (Google Calendar)
- [ ] Интеграция с платёжной системой
- [ ] Telegram-бот для записи

### Технические
- [ ] Настроить автоматическое обновление через Git hook
- [ ] Добавить HTTPS для www поддомена
- [ ] Настроить редирект с HTTP на HTTPS
- [ ] Добавить страницу 404
- [ ] Настроить кэширование статики

### Маркетинг
- [ ] Яндекс.Метрика цели (заполнение формы, клики)
- [ ] Google Analytics (опционально)
- [ ] SEO оптимизация
- [ ] robots.txt и sitemap.xml

---

## 📞 Контакты для связи

**Владелец:** g1811kostukiv@yandex.ru  
**GitHub:** vechniystudent86-alt  

---

## 🛠 Полезные команды

### 🚀 Быстрый деплой (Windows)
```cmd
# Запустить скрипт деплоя
deploy.bat
```
**Что делает скрипт:**
1. Копирует файлы на сервер (index.html, styles.css, script.js, hero-photo.png)
2. Применяет изменения в `/var/www/zumba-site/`
3. Перезагружает nginx

### Локально (Windows PowerShell)
```powershell
# Отправить изменения
cd C:\Users\lord0\OneDrive\Desktop\Python\Sanika\zumba-site
git add .
git commit -m "Описание"
git push

# Открыть сайт локально
start index.html
```

### На сервере (SSH)
```bash
# Подключение
ssh root@85.198.64.110

# Обновить сайт
cd ~/zumba-site
git pull
cp -r ~/zumba-site/* /var/www/zumba-site/

# Проверить nginx
nginx -t
systemctl restart nginx
systemctl status nginx

# Логи nginx
tail -20 /var/log/nginx/error.log
tail -20 /var/log/nginx/access.log

# SSL сертификат
certbot renew --dry-run
certbot certificates
```

### Проверка DNS
```powershell
# Windows
nslookup zumba-spb.ru
nslookup www.zumba-spb.ru

# Linux
dig zumba-spb.ru +short
```

---

## 📅 Дата последнего обновления

**26 февраля 2026 г. — Версия 2.0: Безопасность и минификация**

---

## 🎯 Версия 2.0 — Что нового

### 🔒 Безопасность (ИСПРАВЛЕНО)

| Проблема | Решение |
|----------|---------|
| API ключи Telegram в JS | ✅ Перемещены на сервер (send-form.php) |
| Отсутствовала валидация | ✅ Серверная валидация данных |
| Нет защиты от спама | ✅ Rate limiting (5 запросов/5 мин) |
| CSRF уязвимость | ✅ Проверка методов и заголовков |

### 📦 Минификация (НОВОЕ)

- ✅ Автоматическая минификация CSS/JS
- ✅ Build-скрипты (PowerShell + NPM)
- ✅ Режим наблюдения (watch mode)
- ✅ Экономия трафика: ~50-70%

### 📁 Новые файлы

- `send-form.php` — серверный обработчик форм
- `package.json` — NPM зависимости
- `build.ps1` — build-скрипт PowerShell
- `deploy.bat` — скрипт локального деплоя
- `deploy-remote.bat` — скрипт деплоя на сервер
- `nginx-php.conf` — конфигурация nginx с PHP
- `.gitignore` — игнорируемые файлы
- `DEPLOYMENT.md` — полная инструкция по развёртыванию

---

## 🚀 Быстрый старт (v2.0)

```bash
# 1. Установка зависимостей
npm install

# 2. Минификация
npm run build

# 3. Деплой
deploy.bat

# 4. На сервере (SSH)
ssh root@85.198.64.110
cd ~/zumba-site && git pull
cp -r ~/zumba-site/* /var/www/zumba-site/
```

**Подробная инструкция:** см. `DEPLOYMENT.md`

---

## 🎯 Следующие шаги

1. **Сайт полностью работает** — HTTPS настроен ✅
2. **Безопасность** — уязвимости исправлены ✅
3. **Минификация** — настроена ✅
4. **Добавить контент** — фото, видео с тренировок
5. **Настроить цели в Яндекс.Метрике** — отслеживание заявок
6. **SEO оптимизация** — robots.txt, sitemap.xml

---

**Для продолжения работы:** Откройте `DEPLOYMENT.md` для подробных инструкций
