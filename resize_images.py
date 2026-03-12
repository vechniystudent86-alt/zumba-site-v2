import os
from PIL import Image

# Проверяем наличие PIL
try:
    from PIL import Image
except ImportError:
    print("❌ PIL не установлен. Установите: pip install Pillow")
    exit(1)

# Конфигурация
SOURCE_FILE = "hero-photo.png"
SIZES = [320, 480, 640, 800]
OUTPUT_DIR = "."

def resize_image(source, size):
    """Создает уменьшенную версию изображения"""
    try:
        with Image.open(source) as img:
            # Конвертируем в RGB если нужно (для PNG с прозрачностью)
            if img.mode in ('RGBA', 'LA', 'P'):
                img = img.convert('RGB')
            
            # Ресайз
            img_resized = img.resize((size, size), Image.Resampling.LANCZOS)
            
            # Сохраняем PNG
            png_path = os.path.join(OUTPUT_DIR, f"hero-photo-{size}.png")
            img_resized.save(png_path, 'PNG', optimize=True, quality=85)
            
            # Сохраняем WebP
            webp_path = os.path.join(OUTPUT_DIR, f"hero-photo-{size}.webp")
            img_resized.save(webp_path, 'WebP', quality=80, method=6)
            
            # Получаем размеры файлов
            png_size = os.path.getsize(png_path)
            webp_size = os.path.getsize(webp_path)
            
            print(f"✅ {size}px: PNG={png_size/1024:.1f}KB, WebP={webp_size/1024:.1f}KB")
            
    except Exception as e:
        print(f"❌ Ошибка при обработке {size}px: {e}")

def main():
    if not os.path.exists(SOURCE_FILE):
        print(f"❌ Файл {SOURCE_FILE} не найден!")
        return
    
    # Получаем размеры оригинала
    with Image.open(SOURCE_FILE) as img:
        print(f"📸 Оригинал: {SOURCE_FILE} ({img.width}x{img.height}px)")
    
    print("\n🔄 Создание оптимизированных версий...")
    
    for size in SIZES:
        resize_image(SOURCE_FILE, size)
    
    # Создаем WebP для основного фото
    with Image.open(SOURCE_FILE) as img:
        if img.mode in ('RGBA', 'LA', 'P'):
            img = img.convert('RGB')
        webp_path = os.path.join(OUTPUT_DIR, "hero-photo.webp")
        img.save(webp_path, 'WebP', quality=85, method=6)
        print(f"✅ hero-photo.webp: {os.path.getsize(webp_path)/1024:.1f}KB")
    
    print("\n✅ Готово!")

if __name__ == "__main__":
    main()
