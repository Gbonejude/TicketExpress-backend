# Script de synchronisation automatique de la collection Postman
# Ce script copie automatiquement la collection générée vers Postman

$sourceCollection = "public\docs\collection.json"
$postmanWorkspace = "$env:APPDATA\Postman\files"

# Créer le dossier Postman files s'il n'existe pas
if (-not (Test-Path $postmanWorkspace)) {
    New-Item -ItemType Directory -Path $postmanWorkspace -Force | Out-Null
}

# Copier la collection
if (Test-Path $sourceCollection) {
    $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
    $targetFile = "$postmanWorkspace\TicketExpress_API_$timestamp.json"
    Copy-Item $sourceCollection -Destination $targetFile -Force
    Write-Host "Collection synchronisee: $targetFile" -ForegroundColor Green
    Write-Host "Ouvrez Postman et importez le fichier" -ForegroundColor Cyan
} else {
    Write-Host "Collection source introuvable" -ForegroundColor Red
}

Write-Host "Pour auto-sync: php artisan scribe:generate" -ForegroundColor Yellow
