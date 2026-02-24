# Zumba Site Deployment Script
# Run: .\deploy.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Zumba Site Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check for Git changes
Write-Host "[1/4] Checking Git status..." -ForegroundColor Yellow
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "Found changes. Committing..." -ForegroundColor Yellow
    git add .
    git commit -m "Auto-commit: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
    Write-Host "Changes committed." -ForegroundColor Green
} else {
    Write-Host "No new changes." -ForegroundColor Green
}

Write-Host ""
Write-Host "[2/4] Pushing to GitHub..." -ForegroundColor Yellow
git push
if ($LASTEXITCODE -ne 0) {
    Write-Host "Error pushing to GitHub!" -ForegroundColor Red
    exit 1
}
Write-Host "Pushed to GitHub!" -ForegroundColor Green

Write-Host ""
Write-Host "[3/4] Connecting to server..." -ForegroundColor Yellow

# Server credentials
$serverPassword = 'Z*5k53vll2oQ'
$serverUser = 'root'
$serverHost = '85.198.64.110'

# Commands to execute on server
$sshCommands = @"
cd ~/zumba-site
git pull
cp -r ~/zumba-site/* /var/www/zumba-site/
chmod -R 755 /var/www/zumba-site/
echo '=== Site successfully updated! ==='
"@

# Check if sshpass is available
$hasSshpass = $false
try {
    $null = Get-Command sshpass -ErrorAction Stop
    $hasSshpass = $true
} catch {
    Write-Host "sshpass not found. Using interactive SSH..." -ForegroundColor Yellow
}

if ($hasSshpass) {
    Write-Host "Executing commands on server..." -ForegroundColor Yellow
    $sshpassCmd = "sshpass -p '$serverPassword' ssh -o StrictHostKeyChecking=no $serverUser@$serverHost `"$sshCommands`""
    Invoke-Expression $sshpassCmd
} else {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Yellow
    Write-Host "  ENTER PASSWORD MANUALLY:" -ForegroundColor Yellow
    Write-Host "  $serverPassword" -ForegroundColor White
    Write-Host "========================================" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Commands to run on server:" -ForegroundColor Cyan
    Write-Host $sshCommands -ForegroundColor White
    Write-Host ""
    Write-Host "Opening SSH session..." -ForegroundColor Yellow
    Start-Sleep -Seconds 2
    
    Start-Process ssh -ArgumentList "$serverUser@$serverHost"
}

Write-Host ""
Write-Host "[4/4] Done!" -ForegroundColor Green
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Site: https://zumba-spb.ru" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
