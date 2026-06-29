# Script de Nettoyage Automatique - TicketExpress Backend
# Date: 2026-06-29
# Action: Nettoyer la racine du projet (150+ fichiers -> 10 fichiers essentiels)

$ErrorActionPreference = "Stop"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  NETTOYAGE AUTOMATIQUE DU PROJET" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Statistiques
$filesDeleted = 0
$filesMoved = 0
$foldersCreated = 0

# Confirmation
Write-Host "Ce script va:" -ForegroundColor Yellow
Write-Host "  1. Creer dossiers docs/ et scripts/" -ForegroundColor White
Write-Host "  2. Deplacer 4 rapports importants dans docs/reports/" -ForegroundColor White
Write-Host "  3. Deplacer 2 scripts utiles dans scripts/" -ForegroundColor White
Write-Host "  4. Supprimer ~140 fichiers obsoletes" -ForegroundColor White
Write-Host ""
Write-Host "ATTENTION: Cette action est IRREVERSIBLE!" -ForegroundColor Red
Write-Host ""

$confirmation = Read-Host "Taper 'OUI' pour continuer"

if ($confirmation -ne 'OUI') {
    Write-Host ""
    Write-Host "Operation annulee." -ForegroundColor Yellow
    Write-Host ""
    exit 0
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  DEMARRAGE DU NETTOYAGE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""

# Phase 1: Creer dossiers
Write-Host "[1/4] Creation des dossiers..." -ForegroundColor Cyan

if (-not (Test-Path "docs")) {
    New-Item -ItemType Directory -Path "docs" | Out-Null
    $foldersCreated++
    Write-Host "  + docs/" -ForegroundColor Green
}

if (-not (Test-Path "docs\reports")) {
    New-Item -ItemType Directory -Path "docs\reports" | Out-Null
    $foldersCreated++
    Write-Host "  + docs/reports/" -ForegroundColor Green
}

if (-not (Test-Path "docs\archive")) {
    New-Item -ItemType Directory -Path "docs\archive" | Out-Null
    $foldersCreated++
    Write-Host "  + docs/archive/" -ForegroundColor Green
}

if (-not (Test-Path "scripts")) {
    New-Item -ItemType Directory -Path "scripts" | Out-Null
    $foldersCreated++
    Write-Host "  + scripts/" -ForegroundColor Green
}

Write-Host ""

# Phase 2: Deplacer rapports importants
Write-Host "[2/4] Deplacement des rapports importants..." -ForegroundColor Cyan

$reportsToKeep = @(
    "MIGRATION_COMPLETE_CLEANUP.md",
    "100_PERCENT_COVERAGE_ATTEINT.md",
    "FIX_PULSE_CACHE_GROUPS.md",
    "SESSION_4_RAPPORT_FINAL.md",
    "CLEANUP_PLAN.md"
)

foreach ($report in $reportsToKeep) {
    if (Test-Path $report) {
        Move-Item -Path $report -Destination "docs\reports\" -Force
        $filesMoved++
        Write-Host "  -> docs/reports/$report" -ForegroundColor Green
    }
}

Write-Host ""

# Phase 3: Deplacer scripts utiles
Write-Host "[3/4] Deplacement des scripts utiles..." -ForegroundColor Cyan

$scriptsToKeep = @(
    "start-worker.ps1",
    "sync-postman.ps1"
)

foreach ($script in $scriptsToKeep) {
    if (Test-Path $script) {
        Move-Item -Path $script -Destination "scripts\" -Force
        $filesMoved++
        Write-Host "  -> scripts/$script" -ForegroundColor Green
    }
}

Write-Host ""

# Phase 4: Supprimer fichiers obsoletes
Write-Host "[4/4] Suppression des fichiers obsoletes..." -ForegroundColor Cyan

# Liste des fichiers a garder absolument a la racine
$filesToKeep = @(
    "README.md",
    "AGENTS.md",
    ".mcp.json",
    "boost.json",
    "_ide_helper.php",
    "_ide_helper_models.php",
    "composer.json",
    "composer.lock",
    "package.json",
    "package-lock.json",
    "artisan",
    ".env",
    ".env.example",
    ".editorconfig",
    ".gitignore",
    ".gitattributes",
    "phpunit.xml",
    "vite.config.js",
    "tailwind.config.js",
    "postcss.config.js",
    "CLEANUP_EXECUTE.ps1"  # Ce script lui-meme
)

# Recuperer tous les fichiers a la racine
$allFiles = Get-ChildItem -Path . -File | Where-Object { 
    $_.Name -notin $filesToKeep 
}

foreach ($file in $allFiles) {
    try {
        Remove-Item -Path $file.FullName -Force
        $filesDeleted++
        Write-Host "  x $($file.Name)" -ForegroundColor Gray
    } catch {
        Write-Host "  ! Erreur suppression: $($file.Name)" -ForegroundColor Red
    }
}

Write-Host ""

# Résumé
Write-Host "========================================" -ForegroundColor Green
Write-Host "  NETTOYAGE TERMINE" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "Statistiques:" -ForegroundColor Cyan
Write-Host "  Dossiers crees: $foldersCreated" -ForegroundColor White
Write-Host "  Fichiers deplaces: $filesMoved" -ForegroundColor White
Write-Host "  Fichiers supprimes: $filesDeleted" -ForegroundColor White
Write-Host ""

# Verifier structure finale
$rootFiles = (Get-ChildItem -Path . -File).Count
Write-Host "Fichiers restants a la racine: $rootFiles" -ForegroundColor Cyan
Write-Host ""

if ($rootFiles -le 20) {
    Write-Host "Structure propre et professionnelle!" -ForegroundColor Green
} else {
    Write-Host "Quelques fichiers supplementaires restent (verifier manuellement)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  STRUCTURE FINALE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Racine/" -ForegroundColor White
Get-ChildItem -Path . -File | Select-Object -First 15 Name | ForEach-Object {
    Write-Host "  $($_.Name)" -ForegroundColor Gray
}

if ($rootFiles -gt 15) {
    Write-Host "  ... et $($rootFiles - 15) autres fichiers" -ForegroundColor Gray
}

Write-Host ""
Write-Host "docs/" -ForegroundColor White
Write-Host "  reports/ ($((Get-ChildItem 'docs\reports' -File).Count) fichiers)" -ForegroundColor Gray
Write-Host "  archive/ ($((Get-ChildItem 'docs\archive' -File).Count) fichiers)" -ForegroundColor Gray

Write-Host ""
Write-Host "scripts/ ($((Get-ChildItem 'scripts' -File).Count) fichiers)" -ForegroundColor Gray

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host "  PROJET NETTOYE AVEC SUCCES!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
