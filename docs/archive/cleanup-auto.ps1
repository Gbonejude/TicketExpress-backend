# Nettoyage Automatique (sans confirmation)
$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  NETTOYAGE AUTOMATIQUE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

$filesDeleted = 0
$filesMoved = 0
$foldersCreated = 0

# Phase 1: Créer dossiers
Write-Host "[1/4] Creation des dossiers..." -ForegroundColor Cyan

if (-not (Test-Path "docs")) {
    New-Item -ItemType Directory -Path "docs" | Out-Null
    $foldersCreated++
}
if (-not (Test-Path "docs\reports")) {
    New-Item -ItemType Directory -Path "docs\reports" | Out-Null
    $foldersCreated++
}
if (-not (Test-Path "docs\archive")) {
    New-Item -ItemType Directory -Path "docs\archive" | Out-Null
    $foldersCreated++
}
if (-not (Test-Path "scripts")) {
    New-Item -ItemType Directory -Path "scripts" | Out-Null
    $foldersCreated++
}

Write-Host "  $foldersCreated dossiers crees" -ForegroundColor Green
Write-Host ""

# Phase 2: Déplacer rapports
Write-Host "[2/4] Deplacement des rapports..." -ForegroundColor Cyan

$reports = @(
    "MIGRATION_COMPLETE_CLEANUP.md",
    "100_PERCENT_COVERAGE_ATTEINT.md",
    "FIX_PULSE_CACHE_GROUPS.md",
    "SESSION_4_RAPPORT_FINAL.md",
    "CLEANUP_PLAN.md"
)

foreach ($r in $reports) {
    if (Test-Path $r) {
        Move-Item $r "docs\reports\" -Force
        $filesMoved++
    }
}

Write-Host "  $filesMoved rapports deplaces" -ForegroundColor Green
Write-Host ""

# Phase 3: Déplacer scripts
Write-Host "[3/4] Deplacement des scripts..." -ForegroundColor Cyan

$scripts = @("start-worker.ps1", "sync-postman.ps1")
foreach ($s in $scripts) {
    if (Test-Path $s) {
        Move-Item $s "scripts\" -Force
        $filesMoved++
    }
}

Write-Host "  Scripts deplaces" -ForegroundColor Green
Write-Host ""

# Phase 4: Supprimer obsolètes
Write-Host "[4/4] Suppression des fichiers obsoletes..." -ForegroundColor Cyan

$keep = @(
    "README.md", "AGENTS.md", ".mcp.json", "boost.json",
    "_ide_helper.php", "_ide_helper_models.php",
    "composer.json", "composer.lock", "package.json", "package-lock.json",
    "artisan", ".env", ".env.example", ".editorconfig",
    ".gitignore", ".gitattributes", "phpunit.xml",
    "vite.config.js", "tailwind.config.js", "postcss.config.js",
    "CLEANUP_EXECUTE.ps1", "cleanup-auto.ps1"
)

Get-ChildItem -File | Where-Object { $_.Name -notin $keep } | ForEach-Object {
    Remove-Item $_.FullName -Force
    $filesDeleted++
}

Write-Host "  $filesDeleted fichiers supprimes" -ForegroundColor Green
Write-Host ""

# Résumé
Write-Host "========================================" -ForegroundColor Green
Write-Host "  TERMINE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Dossiers crees: $foldersCreated" -ForegroundColor White
Write-Host "Fichiers deplaces: $filesMoved" -ForegroundColor White
Write-Host "Fichiers supprimes: $filesDeleted" -ForegroundColor White
Write-Host ""

$remaining = (Get-ChildItem -File).Count
Write-Host "Fichiers restants a la racine: $remaining" -ForegroundColor Cyan
Write-Host ""
