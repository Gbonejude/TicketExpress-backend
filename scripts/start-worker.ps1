# Script de démarrage du worker pour les files d'attente TicketExpress

Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "  TicketExpress Queue Worker Starter     " -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si nous sommes dans le bon répertoire
if (-Not (Test-Path "artisan")) {
    Write-Host "ERREUR: Vous devez exécuter ce script depuis le répertoire racine de Laravel" -ForegroundColor Red
    Write-Host "Répertoire actuel: $(Get-Location)" -ForegroundColor Yellow
    exit 1
}

# Afficher la configuration
Write-Host "Configuration actuelle:" -ForegroundColor Green
php artisan queue:monitor
Write-Host ""

# Options de démarrage
Write-Host "Options de démarrage:" -ForegroundColor Green
Write-Host "1. Worker unique (toutes les files)" -ForegroundColor Yellow
Write-Host "2. Worker emails seulement (priorité haute)" -ForegroundColor Yellow
Write-Host "3. Worker notifications seulement (priorité moyenne)" -ForegroundColor Yellow
Write-Host "4. Worker par défaut seulement (priorité basse)" -ForegroundColor Yellow
Write-Host "5. Worker avec supervisor (production)" -ForegroundColor Yellow
Write-Host ""

$choice = Read-Host "Choisissez une option (1-5)"

switch ($choice) {
    "1" {
        Write-Host "Démarrage du worker unique..." -ForegroundColor Green
        php artisan queue:work --queue=emails,notifications,default --sleep=3 --tries=3 --timeout=60
    }
    "2" {
        Write-Host "Démarrage du worker emails..." -ForegroundColor Green
        php artisan queue:work --queue=emails --sleep=3 --tries=3 --timeout=60
    }
    "3" {
        Write-Host "Démarrage du worker notifications..." -ForegroundColor Green
        php artisan queue:work --queue=notifications --sleep=5 --tries=3 --timeout=60
    }
    "4" {
        Write-Host "Démarrage du worker par défaut..." -ForegroundColor Green
        php artisan queue:work --queue=default --sleep=10 --tries=3 --timeout=60
    }
    "5" {
        Write-Host "Configuration supervisor (production):" -ForegroundColor Green
        Write-Host ""
        Write-Host "Créer le fichier /etc/supervisor/conf.d/ticketexpress.conf:" -ForegroundColor Yellow
        Write-Host ""
        Write-Host "[program:ticketexpress-worker]" -ForegroundColor Cyan
        Write-Host "process_name=%(program_name)s_%(process_num)02d" -ForegroundColor Cyan
        Write-Host "command=php $(Get-Location)\artisan queue:work --queue=emails,notifications,default --sleep=3 --tries=3" -ForegroundColor Cyan
        Write-Host "autostart=true" -ForegroundColor Cyan
        Write-Host "autorestart=true" -ForegroundColor Cyan
        Write-Host "user=www-data" -ForegroundColor Cyan
        Write-Host "numprocs=4" -ForegroundColor Cyan
        Write-Host "redirect_stderr=true" -ForegroundColor Cyan
        Write-Host "stdout_logfile=$(Get-Location)\storage\logs\worker.log" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "Commandes supervisor:" -ForegroundColor Yellow
        Write-Host "sudo supervisorctl reread" -ForegroundColor Cyan
        Write-Host "sudo supervisorctl update" -ForegroundColor Cyan
        Write-Host "sudo supervisorctl start ticketexpress-worker:*" -ForegroundColor Cyan
    }
    default {
        Write-Host "Option invalide. Démarrage par défaut..." -ForegroundColor Red
        php artisan queue:work --queue=emails,notifications,default --sleep=3 --tries=3 --timeout=60
    }
}

Write-Host ""
Write-Host "Worker démarré. Appuyez sur Ctrl+C pour arrêter." -ForegroundColor Green