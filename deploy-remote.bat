@echo off
chcp 65001 >nul
REM Деплой файлов на сервер через SSH
REM Требует настроенный SSH доступ без пароля (ключи)

set SERVER=root@85.198.64.110
set REMOTE_DIR=~/zumba-site
set WEB_DIR=/var/www/zumba-site

echo ============================================
echo   Zumba Site - Деплой на сервер
echo ============================================
echo.

echo [INFO] Проверка подключения к серверу...
ssh %SERVER% "echo OK" >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Не удалось подключиться к серверу
    echo Проверьте SSH ключи или пароль
    pause
    exit /b 1
)

echo [INFO] Подключение успешно!
echo.
echo [INFO] Обновление файлов на сервере...

REM Копирование файлов
scp index.html styles.css styles.min.css responsive.css responsive.min.css script.js script.min.js send-form.php package.json build.ps1 %SERVER%:%REMOTE_DIR%/
if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Ошибка копирования файлов
    pause
    exit /b 1
)

echo [INFO] Файлы скопированы!
echo.
echo [INFO] Применение изменений на сервере...

REM Выполнение команд на сервере
ssh %SERVER% ^
    "cd %REMOTE_DIR% && ^
     git pull 2>/dev/null || true && ^
     cp -r %REMOTE_DIR%/* %WEB_DIR%/ && ^
     chown -R www-data:www-data %WEB_DIR% && ^
     chmod 644 %WEB_DIR%/*.php && ^
     systemctl reload nginx"

if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Ошибка применения изменений
    pause
    exit /b 1
)

echo.
echo ============================================
echo   Деплой завершён успешно!
echo ============================================
echo.
echo Сайт обновлён: https://zumba-spb.ru
echo.
pause
