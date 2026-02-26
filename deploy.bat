@echo off
chcp 65001 >nul
REM Автоматический деплой на сервер с минификацией
REM Zumba Site v2.0

echo ============================================
echo   Zumba Site - Деплой на сервер
echo ============================================
echo.

REM Проверка Node.js
where node >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Node.js не найден!
    echo Установите Node.js или выполните: npm install
    pause
    exit /b 1
)

REM Проверка зависимостей
if not exist "node_modules" (
    echo [INFO] Установка зависимостей...
    call npm install
    if %ERRORLEVEL% neq 0 (
        echo [ОШИБКА] Ошибка установки зависимостей
        pause
        exit /b 1
    )
)

REM Минификация
echo.
echo [INFO] Минификация CSS и JS...
call npm run build
if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Ошибка минификации
    pause
    exit /b 1
)

echo.
echo [INFO] Проверка Git...
git status >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo [ОШИБКА] Это не Git репозиторий
    pause
    exit /b 1
)

REM Коммит и пуш
echo.
set /p COMMIT_MSG="Введите сообщение для коммита (или нажмите Enter для пропуска): "
if not "%COMMIT_MSG%"=="" (
    echo [INFO] Добавление файлов...
    git add .
    
    echo [INFO] Коммит изменений...
    git commit -m "%COMMIT_MSG%"
    
    echo [INFO] Отправка на сервер...
    git push
    
    if %ERRORLEVEL% neq 0 (
        echo [ОШИБКА] Ошибка отправки в Git
        pause
        exit /b 1
    )
)

echo.
echo ============================================
echo   Деплой завершён!
echo ============================================
echo.
echo Следующие шаги:
echo 1. Подключитесь к серверу: ssh root@85.198.64.110
echo 2. Обновите файлы: cd ~/zumba-site ^&^& git pull
echo 3. Скопируйте: cp -r ~/zumba-site/* /var/www/zumba-site/
echo.
echo Или выполните команду deploy-remote.bat для авто-деплоя
echo.
pause
