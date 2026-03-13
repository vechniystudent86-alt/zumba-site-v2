@echo off
REM Деплой на сервер с проверкой PHP

echo ========================================
echo Zumba Site - Деплой на сервер
echo ========================================
echo.

REM 1. Коммит изменений
echo [1/4] Коммит изменений...
cd /d C:\Users\lord0\OneDrive\Desktop\Python\Sanika\zumba-site
git add .
git commit -m "Update: fixes and improvements"
git push
if errorlevel 1 (
    echo ERROR: Не удалось запушить изменения!
    pause
    exit /b 1
)

echo.
echo [2/4] Изменения запушены в GitHub
echo.
echo ========================================
echo СЛЕДУЮЩИЕ ШАГИ НА СЕРВЕРЕ:
echo ========================================
echo.
echo 1. Подключитесь к серверу:
echo    ssh root@85.198.64.110
echo.
echo 2. Обновите файлы:
echo    cd ~/zumba-site
echo    git pull
echo    cp -r ~/zumba-site/* /var/www/zumba-site/
echo.
echo 3. Проверьте PHP:
echo    systemctl status php8.1-fpm
echo.
echo 4. Проверьте nginx конфиг:
echo    nano /etc/nginx/sites-available/zumba-site
echo.
echo 5. Перезапустите службы:
echo    nginx -t
echo    systemctl restart nginx
echo    systemctl restart php8.1-fpm
echo.
echo 6. Проверьте права:
echo    chown -R www-data:www-data /var/www/zumba-site
echo    chmod 644 /var/www/zumba-site/admin/index.php
echo.
echo ========================================
echo.

pause
