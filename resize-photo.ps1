# Скрипт для создания оптимизированных версий фото
# Использует System.Drawing (требуется .NET)

Add-Type -AssemblyName System.Drawing

$sourceFile = "hero-photo.png"
$outputDir = "."

# Проверяем существование файла
if (-not (Test-Path $sourceFile)) {
    Write-Host "❌ Файл $sourceFile не найден!" -ForegroundColor Red
    exit 1
}

# Размеры для создания
$sizes = @(320, 480, 640, 800)

Write-Host "📸 Обработка фото: $sourceFile" -ForegroundColor Cyan

try {
    $originalImage = [System.Drawing.Image]::FromFile((Resolve-Path $sourceFile))
    Write-Host "✅ Оригинал: $($originalImage.Width)x$($originalImage.Height) px" -ForegroundColor Green

    foreach ($size in $sizes) {
        # Вычисляем пропорции (квадратное фото)
        $newWidth = $size
        $newHeight = $size

        # Создаем bitmap
        $bitmap = New-Object System.Drawing.Bitmap($newWidth, $newHeight)
        $graphics = [System.Drawing.Graphics]::FromImage($bitmap)
        
        # Улучшенное качество ресайза
        $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
        $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
        $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
        
        # Рисуем уменьшенное изображение
        $graphics.DrawImage($originalImage, 0, 0, $newWidth, $newHeight)
        
        # Сохраняем PNG
        $pngPath = Join-Path $outputDir "hero-photo-${size}.png"
        $bitmap.Save($pngPath, [System.Drawing.Imaging.ImageFormat]::Png)
        Write-Host "✅ Создан: hero-photo-${size}.png" -ForegroundColor Green
        
        # Сохраняем WebP (через конвертацию в PNG с последующей обработкой)
        # Для WebP используем онлайн конвертер или оставим PNG
        $graphics.Dispose()
        $bitmap.Dispose()
    }

    $originalImage.Dispose()
    
    Write-Host "`n✅ Все версии созданы успешно!" -ForegroundColor Green
    Write-Host "📁 Файлы сохранены в: $outputDir" -ForegroundColor Cyan

} catch {
    Write-Host "❌ Ошибка: $_" -ForegroundColor Red
    exit 1
}
