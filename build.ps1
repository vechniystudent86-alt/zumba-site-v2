#!/usr/bin/env pwsh
# Build script для минификации CSS и JS
# Требует установленный Node.js с terser и clean-css-cli

param(
    [switch]$Watch,
    [switch]$Help
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$CSSSource = Join-Path $ProjectRoot "styles.css"
$CSSResponsive = Join-Path $ProjectRoot "responsive.css"
$JSSource = Join-Path $ProjectRoot "script.js"

$CSSOutput = Join-Path $ProjectRoot "styles.min.css"
$CSSResponsiveOutput = Join-Path $ProjectRoot "responsive.min.css"
$JSOutput = Join-Path $ProjectRoot "script.min.js"

function Write-Info {
    param([string]$Message)
    Write-Host "[BUILD] $Message" -ForegroundColor Cyan
}

function Write-Success {
    param([string]$Message)
    Write-Host "[BUILD] ✓ $Message" -ForegroundColor Green
}

function Write-Error {
    param([string]$Message)
    Write-Host "[BUILD] ✗ $Message" -ForegroundColor Red
}

function Test-Dependencies {
    $deps = @('terser', 'cleancss')
    $missing = @()
    
    foreach ($dep in $deps) {
        if (-not (Get-Command $dep -ErrorAction SilentlyContinue)) {
            $missing += $dep
        }
    }
    
    if ($missing.Count -gt 0) {
        Write-Error "Отсутствуют зависимости: $($missing -join ', ')"
        Write-Info "Установите через: npm install -g terser clean-css-cli"
        exit 1
    }
}

function Minify-CSS {
    param(
        [string]$Input,
        [string]$Output
    )
    
    Write-Info "Минификация CSS: $(Split-Path -Leaf $Input)"
    
    $args = @(
        '--output', $Output
        '--skip-rebase'
        $Input
    )
    
    & cleancss @args
    
    if ($LASTEXITCODE -eq 0) {
        $originalSize = (Get-Item $Input).Length
        $minifiedSize = (Get-Item $Output).Length
        $savings = [math]::Round((1 - $minifiedSize / $originalSize) * 100, 1)
        Write-Success "$(Split-Path -Leaf $Output) ($([math]::Round($minifiedSize/1KB, 2)) KB, экономия ${savings}%)"
    } else {
        Write-Error "Ошибка минификации $(Split-Path -Leaf $Input)"
    }
}

function Minify-JS {
    param(
        [string]$Input,
        [string]$Output
    )
    
    Write-Info "Минификация JS: $(Split-Path -Leaf $Input)"
    
    $args = @(
        $Input
        '--output' $Output
        '--compress' 'arguments=true,arrows=true,booleans=true,dead_code=true,ecma=2020,evaluate=true,if_return=true,loops=true,merge_vars=true,properties=true,reduce_vars=true,toplevel=true,unused=true'
        '--mangle' 'toplevel=true'
        '--format' 'comments=false'
        '--toplevel'
    )
    
    & terser @args
    
    if ($LASTEXITCODE -eq 0) {
        $originalSize = (Get-Item $Input).Length
        $minifiedSize = (Get-Item $Output).Length
        $savings = [math]::Round((1 - $minifiedSize / $originalSize) * 100, 1)
        Write-Success "$(Split-Path -Leaf $Output) ($([math]::Round($minifiedSize/1KB, 2)) KB, экономия ${savings}%)"
    } else {
        Write-Error "Ошибка минификации $(Split-Path -Leaf $Input)"
    }
}

function Build-All {
    Write-Info "Начало сборки..."
    Write-Host ""
    
    Minify-CSS -Input $CSSSource -Output $CSSOutput
    Minify-CSS -Input $CSSResponsive -Output $CSSResponsiveOutput
    Minify-JS -Input $JSSource -Output $JSOutput
    
    Write-Host ""
    Write-Success "Сборка завершена!"
}

function Watch-Files {
    Write-Info "Режим наблюдения за изменениями..."
    Write-Info "Нажмите Ctrl+C для остановки"
    Write-Host ""
    
    $watcher = New-Object System.IO.FileSystemWatcher
    $watcher.Path = $ProjectRoot
    $watcher.Filter = "*.css,*.js"
    $watcher.IncludeSubdirectories = $false
    $watcher.EnableRaisingEvents = $true
    
    $debounce = $false
    
    $action = {
        if ($debounce) { return }
        $script:debounce = $true
        
        Start-Sleep -Milliseconds 500
        
        $path = $Event.SourceEventArgs.FullPath
        $name = $Event.SourceEventArgs.Name
        Write-Info "Изменён файл: $name"
        
        try {
            if ($name -eq "styles.css") {
                Minify-CSS -Input $CSSSource -Output $CSSOutput
            }
            elseif ($name -eq "responsive.css") {
                Minify-CSS -Input $CSSResponsive -Output $CSSResponsiveOutput
            }
            elseif ($name -eq "script.js") {
                Minify-JS -Input $JSSource -Output $JSOutput
            }
        }
        catch {
            Write-Error $_.Exception.Message
        }
        
        $script:debounce = $false
    }
    
    Register-ObjectEvent -InputObject $watcher -EventName "Changed" -Action $action | Out-Null
    
    try {
        while ($true) {
            Start-Sleep -Milliseconds 100
        }
    }
    finally {
        Unregister-Event -SourceIdentifier * -ErrorAction SilentlyContinue
        $watcher.Dispose()
    }
}

# Help
if ($Help) {
    Write-Host @"
Build Script для Zumba Site

Использование:
  .\build.ps1           - Минифицировать все файлы
  .\build.ps1 -Watch    - Режим наблюдения (авто-сборка при изменениях)
  .\build.ps1 -Help     - Показать эту справку

Требования:
  - Node.js
  - npm install -g terser clean-css-cli
"@ -ForegroundColor White
    exit 0
}

# Main
Write-Host ""
Write-Host "====================================" -ForegroundColor Magenta
Write-Host "  Zumba Site Build Tool" -ForegroundColor Magenta
Write-Host "====================================" -ForegroundColor Magenta
Write-Host ""

Test-Dependencies

if ($Watch) {
    Build-All
    Write-Host ""
    Watch-Files
} else {
    Build-All
}
