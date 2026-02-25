@echo off
chcp 65001 >nul
echo ========================================
echo   Деплой сайта на сервер (Beget)
echo   zumba-spb.ru
echo ========================================
echo.

set PLINK=C:\Users\lord0\plink.exe
set PSCP=C:\Users\lord0\pscp.exe
set HOST=85.198.64.110
set USER=root
set PASS=Z*5k53vll2oQ
set HOSTKEY=ssh-ed25519 255 SHA256:+OBwjHWvUplcWSE6bzIbvVBxpMunKkN+deYBN1S3G6Y
set LOCAL_DIR=C:\Users\lord0\OneDrive\Desktop\Python\Sanika\zumba-site
set REMOTE_DIR=/root/zumba-site
set WEB_DIR=/var/www/zumba-site

echo [1/3] Копирование файлов на сервер...
%PSCP% -pw "%PASS%" -hostkey "%HOSTKEY%" "%LOCAL_DIR%\index.html" "%USER%@%HOST%:%REMOTE_DIR%/"
%PSCP% -pw "%PASS%" -hostkey "%HOSTKEY%" "%LOCAL_DIR%\styles.css" "%USER%@%HOST%:%REMOTE_DIR%/"
%PSCP% -pw "%PASS%" -hostkey "%HOSTKEY%" "%LOCAL_DIR%\responsive.css" "%USER%@%HOST%:%REMOTE_DIR%/"
%PSCP% -pw "%PASS%" -hostkey "%HOSTKEY%" "%LOCAL_DIR%\script.js" "%USER%@%HOST%:%REMOTE_DIR%/"
%PSCP% -pw "%PASS%" -hostkey "%HOSTKEY%" "%LOCAL_DIR%\hero-photo.png" "%USER%@%HOST%:%REMOTE_DIR%/"

echo.
echo [2/3] Применение изменений в веб-директорию...
%PLINK% -ssh -P 22 -batch -hostkey "%HOSTKEY%" %USER%@%HOST% -pw "%PASS%" "cp -r %REMOTE_DIR%/* %WEB_DIR%/"

echo.
echo [3/3] Перезагрузка nginx...
%PLINK% -ssh -P 22 -batch -hostkey "%HOSTKEY%" %USER%@%HOST% -pw "%PASS%" "systemctl reload nginx"

echo.
echo ========================================
echo   Деплой завершён успешно!
echo   Сайт: https://zumba-spb.ru
echo ========================================
echo.
pause
