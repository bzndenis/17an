# Siapkan project untuk upload ke Niagahoster (jalankan di Windows/Laragon)
# Usage: powershell -ExecutionPolicy Bypass -File deploy/prepare-niagahoster.ps1

$ErrorActionPreference = "Stop"
$root = Split-Path -Parent $PSScriptRoot
Set-Location $root

Write-Host "==> 17an Dashboard - Prepare for Niagahoster" -ForegroundColor Cyan

Write-Host "-> composer install --no-dev --optimize-autoloader"
composer install --no-dev --optimize-autoloader --no-interaction

Write-Host "-> npm install and npm run build"
npm install --ignore-scripts
npm run build

Write-Host "-> php artisan optimize (local check)"
php artisan config:clear
php artisan view:clear

$zipPath = Join-Path $root "deploy\17an-upload.zip"
if (Test-Path $zipPath) { Remove-Item $zipPath -Force }

Write-Host "-> Membuat arsip upload..."

$storageDirs = @(
    "storage/app/public",
    "storage/framework/cache/data",
    "storage/framework/sessions",
    "storage/framework/views",
    "storage/logs"
)
foreach ($d in $storageDirs) {
    New-Item -ItemType Directory -Force -Path (Join-Path $root $d) | Out-Null
}

$items = @(
    "app", "bootstrap", "config", "database", "public", "resources", "routes",
    "storage", "vendor", "artisan", "composer.json", "composer.lock",
    ".env.production.example", "deploy", "DEPLOY-NIAGAHOSTER.md"
)

Compress-Archive -Path ($items | ForEach-Object { Join-Path $root $_ }) -DestinationPath $zipPath -Force

Write-Host ""
Write-Host "Selesai!" -ForegroundColor Green
Write-Host "Arsip siap upload: $zipPath"
Write-Host "Upload via File Manager/FTP, extract, buat .env, ikuti DEPLOY-NIAGAHOSTER.md"
