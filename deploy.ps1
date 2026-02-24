# Скрипт деплоя сайта на сервер
# Запуск: .\deploy.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Деплой сайта Zumba на сервер" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Проверяем, есть ли изменения для коммита
Write-Host "[1/4] Проверка изменений Git..." -ForegroundColor Yellow
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "Найдены изменения. Коммитим..." -ForegroundColor Yellow
    git add .
    git commit -m "Авто-коммит: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    Write-Host "Изменения закоммичены." -ForegroundColor Green
} else {
    Write-Host "Нет новых изменений." -ForegroundColor Green
}

Write-Host ""
Write-Host "[2/4] Отправка в GitHub..." -ForegroundColor Yellow
git push
if ($LASTEXITCODE -ne 0) {
    Write-Host "Ошибка при push в GitHub!" -ForegroundColor Red
    exit 1
}
Write-Host "Отправлено в GitHub!" -ForegroundColor Green

Write-Host ""
Write-Host "[3/4] Подключение к серверу..." -ForegroundColor Yellow

# Пароль сервера
$serverPassword = 'Z*5k53vll2oQ'
$serverUser = 'root'
$serverHost = '85.198.64.110'

# Команды для выполнения на сервере
$sshCommands = @"
cd ~/zumba-site
git pull
cp -r ~/zumba-site/* /var/www/zumba-site/
chmod -R 755 /var/www/zumba-site/
echo '=== Сайт успешно обновлён! ==='
"@

# Проверяем, есть ли sshpass
$hasSshpass = $false
try {
    $null = Get-Command sshpass -ErrorAction Stop
    $hasSshpass = $true
} catch {
    Write-Host "sshpass не найден. Используем интерактивный SSH..." -ForegroundColor Yellow
}

if ($hasSshpass) {
    # Используем sshpass для автоматического ввода пароля
    Write-Host "Выполнение команд на сервере..." -ForegroundColor Yellow
    $sshpassCmd = "sshpass -p '$serverPassword' ssh -o StrictHostKeyChecking=no $serverUser@$serverHost `"$sshCommands`""
    Invoke-Expression $sshpassCmd
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Yellow
    Write-Host "  ВВЕДИТЕ ПАРОЛЬ ВРУЧНУЮ:" -ForegroundColor Yellow
    Write-Host "  $serverPassword" -ForegroundColor White
    Write-Host "========================================" -ForegroundColor Yellow
    Write-Host ""
    
    # Используем стандартный SSH (интерактивный)
    $sshCmd = "ssh $serverUser@$serverHost"
    Write-Host "Выполнение: $sshCmd" -ForegroundColor Yellow
    Write-Host "Команды для выполнения на сервере:" -ForegroundColor Cyan
    Write-Host $sshCommands -ForegroundColor White
    Write-Host ""
    Write-Host "Открываю SSH сессию..." -ForegroundColor Yellow
    Start-Sleep -Seconds 2
    
    # Открываем SSH сессию
    Start-Process ssh -ArgumentList "$serverUser@$serverHost"
}

Write-Host ""
Write-Host "[4/4] Готово!" -ForegroundColor Green
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Сайт доступен: https://zumba-spb.ru" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
