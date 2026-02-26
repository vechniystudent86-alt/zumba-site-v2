@echo off
chcp 65001 >nul
REM Автоматический деплой через SSH
REM Требует: ssh-keygen, ssh-agent, добавленный ключ на сервер

set SERVER=root@85.198.64.110
set REMOTE_DIR=~/zumba-site
set WEB_DIR=/var/www/zumba-site

echo ============================================
echo   Zumba Site - Авто-деплей
echo ============================================
echo.

REM Проверка Git
git status >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Не Git репозиторий
    pause
    exit /b 1
)

REM Коммит и пуш
echo [INFO] Коммит изменений...
git add .
git commit -m "Auto-deploy: %DATE% %TIME%"
git push

if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Ошибка Git push
    pause
    exit /b 1
)

echo [INFO] Успешно отправлено в Git!
echo.
echo [INFO] Подключение к серверу...

REM Выполнение команд на сервере
ssh %SERVER% "cd %REMOTE_DIR% ^&^& git pull ^&^& cp -r %REMOTE_DIR%/* %WEB_DIR%/ ^&^& chown -R www-data:www-data %WEB_DIR% ^&^& chmod 644 %WEB_DIR%/send-form.php ^&^& systemctl reload nginx"

if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Ошибка применения на сервере
    pause
    exit /b 1
)

echo.
echo ============================================
echo   Деплой завершён успешно!
echo ============================================
echo.
echo Сайт: https://zumba-spb.ru
echo.
pause
