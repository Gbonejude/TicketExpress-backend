# 🧹 Plan de Nettoyage des Fichiers Racine

**Date:** 2026-06-29  
**Objectif:** Nettoyer la racine du projet pour une structure propre et professionnelle

---

## 📊 Inventaire (150+ fichiers!)

### ✅ À GARDER (Essentiels)

#### Documentation Principale
- `README.md` - Documentation principale du projet
- `AGENTS.md` - Documentation agents (utilisé par Kiro)
- `.mcp.json` - Configuration MCP
- `boost.json` - Configuration Laravel Boost

#### Rapports Finaux (Les Plus Récents)
- `MIGRATION_COMPLETE_CLEANUP.md` - Rapport migration cleanup
- `100_PERCENT_COVERAGE_ATTEINT.md` - Milestone 100% coverage
- `FIX_PULSE_CACHE_GROUPS.md` - Fix Pulse récent
- `SESSION_4_RAPPORT_FINAL.md` - Session la plus récente

#### Scripts Utiles
- `start-worker.ps1` - Démarrage queue workers
- `sync-postman.ps1` - Sync collection Postman

#### Helpers IDE
- `_ide_helper.php` - Laravel IDE Helper
- `_ide_helper_models.php` - Models autocomplete

---

### 🗑️ À SUPPRIMER (Obsolètes)

#### 1. Rapports de Test Anciens (30+ fichiers)
- `API_TEST_*.md` (tous sauf le plus récent)
- `SESSION_SUMMARY_*.md` (anciens)
- `FINAL_*_REPORT.md` (multiples versions)
- `ENDPOINT_TEST_*.md`
- `REMAINING_ENDPOINTS_*.md`
- `TESTS_*_REPORT.md`

#### 2. Scripts de Test Obsolètes (40+ fichiers)
- `test-*.ps1` (tous les scripts de test temporaires)
- `test-*.php` (scripts de debug PHP)
- `debug-*.php`
- `debug-*.ps1`
- `fix-*.ps1`
- `generate-*.php`

#### 3. Fichiers JSON Temporaires
- `test-results*.json`
- `phpstan-*.json`
- `errors-to-fix.json`
- `api_routes*.txt/json`
- `ticket-types-response.json`

#### 4. Documentation Redondante/Obsolète
- `ADVANCED_EVENT_FEATURES_SPEC.md`
- `AVAILABILITY_STATUS_*.md`
- `CHECKIN_AND_PROMOTIONS_*.md`
- `CLIENT_REGISTRATION_*.md`
- `COMPLETE_FIXES_*.md`
- `COMPREHENSIVE_*.md`
- `CONTROLLERS_ACTIONS_*.md`
- `DATABASE_PERFORMANCE_*.md`
- `E2E_*.md` (multiples)
- `EMAIL_*.md`
- `EVENTS_*.md` (garder un seul)
- `FEATURES_TODO.md` (obsolète)
- `FILES_CREATED_*.md`
- `FIXES_*.md`
- `FULL_AUTO_*.md`
- `GEMINI.md`, `CLAUDE.md` (config AI)
- `MEDIA_MANAGEMENT_*.md`
- `MONITORING_*.md`
- `MULTI_DATE_*.md`
- `NEXT_STEP*.md`
- `ORDER_TIMESTAMPS_*.md`
- `ORGANIZER_MANAGER_*.md`
- `PERFORMANCE_*.md`
- `PHASE_*.md`
- `PHPSTAN_*.md`
- `POSTMAN_*.md`
- `QUEUE_WORKER_*.md`
- `QUICK_*.md`
- `RBAC_*.md`
- `README_*.md` (sauf README.md principal)
- `REFUND_POLICY_*.md`
- `REGISTRATION_*.md`
- `REPONSES_*.md`
- `RICH_DESCRIPTION_*.md`
- `SCRIBE_FIX_*.md`
- `SETUP_*.md`
- `START_HERE_*.md`
- `TESTING_GUIDE.md`
- `TICKET_DOWNLOAD_*.md`
- `TICKETEXPRESS_*.md`
- `TODO_*.md`
- `ULTRA_FINAL_*.md`
- `VALIDATION_*.md`

#### 5. Scripts Divers
- `all-*.txt`
- `show-progress.ps1`
- `run-all-*.ps1`
- `complete-remaining-tests.ps1`
- `convert_*.php`
- `improved_converter.php`
- `final_converter.php`
- `clean_test.php`
- `test_original.php`
- `identify-missing-endpoints.ps1`
- `find-missing-3.ps1`

#### 6. Fichiers PHPStan Obsolètes
- `phpstan-*.json/txt` (tous)

---

## 📁 Structure Proposée Après Nettoyage

```
TicketExpress-backend/
├── README.md                          ← Documentation principale
├── AGENTS.md                          ← Config agents Kiro
├── .mcp.json                          ← Config MCP
├── boost.json                         ← Config Laravel Boost
├── _ide_helper.php                    ← IDE support
├── _ide_helper_models.php             ← Models autocomplete
├── /docs/                             ← NOUVEAU DOSSIER
│   ├── reports/                       
│   │   ├── MIGRATION_COMPLETE_CLEANUP.md
│   │   ├── 100_PERCENT_COVERAGE_ATTEINT.md
│   │   ├── FIX_PULSE_CACHE_GROUPS.md
│   │   └── SESSION_4_RAPPORT_FINAL.md
│   └── archive/                       ← Garder anciens docs si nécessaire
│       └── (anciens rapports)
├── /scripts/                          ← NOUVEAU DOSSIER
│   ├── start-worker.ps1
│   └── sync-postman.ps1
├── composer.json
├── package.json
├── artisan
├── .env
├── .env.example
└── (dossiers Laravel standards)
```

---

## 🎯 Action Plan

### Phase 1: Créer Dossiers
```bash
mkdir docs
mkdir docs\reports
mkdir docs\archive
mkdir scripts
```

### Phase 2: Déplacer Fichiers à Garder
```bash
# Déplacer rapports importants
move MIGRATION_COMPLETE_CLEANUP.md docs\reports\
move 100_PERCENT_COVERAGE_ATTEINT.md docs\reports\
move FIX_PULSE_CACHE_GROUPS.md docs\reports\
move SESSION_4_RAPPORT_FINAL.md docs\reports\

# Déplacer scripts utiles
move start-worker.ps1 scripts\
move sync-postman.ps1 scripts\
```

### Phase 3: Archiver (optionnel)
Si vous voulez garder l'historique:
```bash
# Déplacer anciens rapports dans archive
move SESSION_*.md docs\archive\
move API_TEST_*.md docs\archive\
move FINAL_*.md docs\archive\
```

### Phase 4: Supprimer le Reste
```bash
# Supprimer tous les scripts de test
del test-*.ps1
del test-*.php
del debug-*.php
del debug-*.ps1
del fix-*.ps1

# Supprimer fichiers JSON temporaires
del test-results*.json
del phpstan-*.json
del errors-to-fix.json

# Supprimer documentation obsolète
del ADVANCED_*.md
del AVAILABILITY_*.md
# ... (tous les autres listés ci-dessus)
```

---

## ⚠️ Recommandation

### Option 1: Nettoyage Agressif (Recommandé)
- ✅ Garder: 10 fichiers essentiels
- 🗑️ Supprimer: ~140 fichiers obsolètes
- 📁 Déplacer: 4-5 rapports importants dans `docs/reports/`
- **Résultat:** Racine propre et professionnelle

### Option 2: Nettoyage Conservateur
- ✅ Garder: 10 fichiers essentiels
- 📁 Déplacer: 20-30 rapports dans `docs/archive/`
- 🗑️ Supprimer: ~110 fichiers vraiment obsolètes
- **Résultat:** Historique préservé, racine propre

### Option 3: Archivage Git
Avant de supprimer, créer un commit:
```bash
git add .
git commit -m "chore: archive old reports and test scripts before cleanup"
```

Puis nettoyer sans crainte (tout est dans l'historique Git).

---

## 📝 Fichiers Essentiels à Garder Absolument

1. `README.md` - Documentation projet
2. `AGENTS.md` - Config Kiro/AI
3. `.mcp.json` - Config MCP
4. `boost.json` - Config Laravel Boost
5. `_ide_helper.php` - IDE support
6. `_ide_helper_models.php` - Models autocomplete
7. `composer.json` - Dependencies PHP
8. `package.json` - Dependencies JS
9. `artisan` - CLI Laravel
10. `.env` / `.env.example` - Configuration

**Total:** 10 fichiers à la racine ✨

---

## 🎯 Commande de Nettoyage Rapide (Option 1)

Voulez-vous que je génère un script PowerShell automatisé pour:
1. Créer les dossiers `docs/` et `scripts/`
2. Déplacer les 4 rapports importants
3. Déplacer les 2 scripts utiles
4. Supprimer tous les fichiers obsolètes

**Confirmation nécessaire avant exécution!**

