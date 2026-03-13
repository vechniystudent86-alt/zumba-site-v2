# 🖼️ Отчёт о замене главного изображения

**Дата:** 13 марта 2026 г.
**Статус:** ✅ Выполнено

---

## 📋 Изменения

### Исходное изображение
- **Файл:** `photo_2026-03-09_22-52-09.jpg`
- **Размер:** Оригинал

### Новое изображение
- **Файл:** `hero-photo.png` (заменён)
- **Размер:** 960×1280px (3:4, вертикальное)

---

## 📁 Созданные файлы

| Файл | Размер | Формат | Назначение |
|------|--------|--------|------------|
| `hero-photo.png` | 960×1280 | PNG | Основное изображение |
| `hero-photo.webp` | 960×1280 | WebP | Основное (оптимизированное) |
| `hero-photo-320.png` | 320×427 | PNG | Мобильные |
| `hero-photo-320.webp` | 320×427 | WebP | Мобильные (оптимизированное) |
| `hero-photo-480.png` | 480×640 | PNG | Планшеты |
| `hero-photo-480.webp` | 480×640 | WebP | Планшеты (оптимизированное) |
| `hero-photo-640.png` | 640×853 | PNG | Десктоп |
| `hero-photo-640.webp` | 640×853 | WebP | Десктоп (оптимизированное) |
| `hero-photo-800.png` | 800×1067 | PNG | Retina |
| `hero-photo-800.webp` | 800×1067 | WebP | Retina (оптимизированное) |

---

## 📊 Сравнение размеров файлов

| Размер | PNG | WebP | Экономия |
|--------|-----|------|----------|
| 320×427 | 170.3 KB | 12.9 KB | **92%** ✅ |
| 480×640 | 328.0 KB | 21.1 KB | **94%** ✅ |
| 640×853 | 513.2 KB | 29.1 KB | **94%** ✅ |
| 800×1067 | 724.4 KB | 38.7 KB | **95%** ✅ |
| 960×1280 | - | 64.8 KB | - |

**Итоговая экономия:** WebP формат экономит ~92-95% места по сравнению с PNG!

---

## 🎨 Где используется

### 1. Hero секция (Главный экран)
```html
<picture>
    <source srcset="hero-photo-320.webp 320w, hero-photo-480.webp 480w, 
                    hero-photo-640.webp 640w, hero-photo-800.webp 800w" 
            sizes="(max-width: 480px) 240px, (max-width: 768px) 360px, 480px" 
            type="image/webp">
    <source srcset="hero-photo-320.png 320w, hero-photo-480.png 480w, 
                    hero-photo-640.png 640w, hero-photo-800.png 800w" 
            sizes="(max-width: 480px) 240px, (max-width: 768px) 360px, 480px" 
            type="image/png">
    <img src="hero-photo.png?v=6" alt="..." width="960" height="1280">
</picture>
```

**Размеры отображения:**
- Мобильные: 240×320px
- Планшеты: 360×480px
- Десктоп: 480×640px

### 2. Секция "О тренере"
```html
<picture>
    <source srcset="hero-photo-320.webp 320w, hero-photo-480.webp 480w, 
                    hero-photo-640.webp 640w" 
            sizes="(max-width: 480px) 240px, (max-width: 768px) 300px, 400px" 
            type="image/webp">
    <source srcset="hero-photo-320.png 320w, hero-photo-480.png 480w, 
                    hero-photo-640.png 640w" 
            sizes="(max-width: 480px) 240px, (max-width: 768px) 300px, 400px" 
            type="image/png">
    <img src="hero-photo.png?v=6" alt="..." width="720" height="960">
</picture>
```

**Размеры отображения:**
- Мобильные: 240×320px
- Планшеты: 300×400px
- Десктоп: 400×533px

---

## 🚀 Применение

### Локально:
```bash
# already done ✅
python resize_images.py
docker-compose restart php web
```

### Очистка кэша:
- **Windows:** `Ctrl + Shift + R`
- **Mac:** `Cmd + Shift + R`

### На сервере:
```bash
# Отправить файлы
git add hero-photo*.png hero-photo*.webp resize_images.py
git commit -m "feat: замена главного изображения"
git push

# На сервере
ssh root@85.198.64.110
cd ~/zumba-site
git pull
python resize_images.py
docker-compose restart php web
```

---

## ✅ Проверка

Откройте **http://localhost:8080** и проверьте:

1. **Главный экран** - новое фото в круглой рамке
2. **Секция "О тренере"** - новое фото в карточке
3. **Мобильная версия** - проверьте на разных размерах экрана

---

## 📝 Примечания

1. **Соотношение сторон:** 3:4 (вертикальное, портретное)
2. **Формат WebP:** Используется для всех современных браузеров
3. **Fallback PNG:** Для старых браузеров
4. **Адаптивность:** 4 размера для разных экранов
5. **Оптимизация:** WebP экономит 92-95% места

---

## 🛠️ Инструменты

### Скрипт для генерации:
```bash
python resize_images.py
```

### Обновить изображение:
1. Замените `hero-photo.png` на новый файл
2. Запустите `python resize_images.py`
3. Перезапустите контейнеры

---

**Исполнитель:** AI Assistant
**Версия:** 3.0
**Дата:** 2026-03-13
