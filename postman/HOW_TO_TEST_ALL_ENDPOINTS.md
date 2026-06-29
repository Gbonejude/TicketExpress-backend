# 🧪 Comment Tester TOUS les Endpoints (84)

**3 méthodes disponibles** pour tester les 84 endpoints de l'API TicketExpress.

---

## 📊 Vue d'Ensemble

| Méthode | Endpoints | Usage | Automation |
|---------|-----------|-------|------------|
| **1. Scénarios Critiques** | 20 | Tests rapides | ✅ MCP + Script |
| **2. Collection Postman** | 82+ | Tests complets | ⚠️ Manuel |
| **3. Documentation Interactive** | 84 | Exploration | ⚠️ Manuel |

---

## ✅ Méthode 1: Scénarios Critiques (Recommandé pour CI/CD)

**Fichier:** `postman/test-scenarios-critical.json`

### Contenu (20 Endpoints)
- ✅ Authentication (3)
- ✅ Events (4)
- ✅ Tickets & Orders (5)
- ✅ Features (5)
- ✅ Admin (3)

### Utilisation

#### Avec Kiro AI (MCP Fetch)
```
"Kiro, teste les scénarios critiques"
```

#### Avec PowerShell
```powershell
.\scripts\test-api-scenarios.ps1
.\scripts\test-api-scenarios.ps1 -Verbose
```

**✅ Avantages:**
- Rapide (< 2 minutes)
- Automatisable
- Couvre 90% des cas d'usage

---

## 📦 Méthode 2: Collection Postman Complète

**Fichier:** `public/docs/collection.json`

### Contenu (82+ Endpoints)
Générée automatiquement par Scribe avec:
- Tous les endpoints documentés
- Exemples de requêtes
- Structures de réponse
- Headers requis

### Utilisation

#### Importer dans Postman
1. Ouvrir Postman
2. File → Import
3. Sélectionner: `public/docs/collection.json`
4. ✅ Collection importée!

#### Tester
- Tests manuels endpoint par endpoint
- Créer des environnements (local, staging, prod)
- Sauvegarder les variables

**✅ Avantages:**
- Interface visuelle
- Debug facile
- Collection officielle à jour

**⚠️ Inconvénients:**
- Tests manuels
- Pas d'automatisation native

---

## 🌐 Méthode 3: Documentation Interactive

**URL:** `http://localhost:8000/docs`

### Contenu (84 Endpoints)
Documentation Scribe avec:
- Interface "Try it out"
- Schémas de requête/réponse
- Exemples en direct
- Code samples (cURL, PHP, JavaScript)

### Utilisation
1. Démarrer le serveur: `php artisan serve`
2. Ouvrir: `http://localhost:8000/docs`
3. Tester directement dans le navigateur

**✅ Avantages:**
- Aucune installation nécessaire
- Documentation toujours à jour
- Tests rapides

**⚠️ Inconvénients:**
- Tests manuels
- Pas de sauvegarde d'état

---

## 🎯 Quelle Méthode Choisir?

### Pour le Développement Quotidien
→ **Scénarios Critiques** (`test-scenarios-critical.json`)
- Tests rapides des flux principaux
- Automatisable

### Pour les Tests Complets
→ **Collection Postman** (`public/docs/collection.json`)
- Import dans Postman
- Tests exhaustifs de tous les endpoints

### Pour la Documentation
→ **Documentation Interactive** (`/docs`)
- Explorer l'API
- Comprendre les schémas
- Tests rapides

---

## 📋 Liste Complète des 84 Endpoints

### Access Control (2)
- GET /api/v1/screens
- GET /api/v1/screens/{key}

### Authentication (8)
- POST /api/v1/auth/send-otp
- POST /api/v1/auth/verify-otp
- POST /api/v1/auth/admin/login
- POST /api/v1/auth/register/client
- POST /api/v1/auth/register/organizer-manager
- POST /api/v1/auth/forgot-password
- POST /api/v1/auth/reset-password
- POST /api/v1/auth/logout

### Coupons (6)
- GET /api/v1/coupons
- POST /api/v1/coupons
- GET /api/v1/coupons/{id}
- PUT /api/v1/coupons/{id}
- DELETE /api/v1/coupons/{id}
- POST /api/v1/coupons/validate

### Event Categories (5)
- GET /api/v1/event-categories
- POST /api/v1/event-categories
- GET /api/v1/event-categories/{id}
- PUT /api/v1/event-categories/{id}
- DELETE /api/v1/event-categories/{id}

### Events (15)
- GET /api/v1/events
- POST /api/v1/events
- GET /api/v1/events/{id}
- PUT /api/v1/events/{id}
- DELETE /api/v1/events/{id}
- POST /api/v1/events/{id}/publish
- POST /api/v1/events/{id}/unpublish
- POST /api/v1/events/{id}/cancel
- GET /api/v1/events/{id}/ticket-types
- POST /api/v1/events/{id}/favorite
- DELETE /api/v1/events/{id}/favorite
- GET /api/v1/favorites
- GET /api/v1/events/{id}/occurrences
- POST /api/v1/events/{id}/occurrences
- GET /api/v1/events/{event}/occurrences/{occurrence}

### Notifications (5)
- GET /api/v1/notifications
- GET /api/v1/notifications/{id}
- POST /api/v1/notifications/{id}/read
- POST /api/v1/notifications/mark-all-read
- DELETE /api/v1/notifications/{id}

### Orders (4)
- GET /api/v1/orders
- POST /api/v1/orders
- GET /api/v1/orders/{id}
- POST /api/v1/orders/{id}/cancel

### Organizers (5)
- GET /api/v1/organizers
- POST /api/v1/organizers
- GET /api/v1/organizers/{id}
- POST /api/v1/organizers/{id}/approve
- POST /api/v1/organizers/{id}/reject

### Permissions (3)
- GET /api/v1/permissions
- POST /api/v1/permissions
- GET /api/v1/permissions/{id}

### Reviews (4)
- GET /api/v1/reviews
- POST /api/v1/reviews
- GET /api/v1/reviews/{id}
- DELETE /api/v1/reviews/{id}

### Roles (6)
- GET /api/v1/roles
- POST /api/v1/roles
- GET /api/v1/roles/{id}
- PUT /api/v1/roles/{id}
- DELETE /api/v1/roles/{id}
- POST /api/v1/roles/{id}/permissions

### Tickets (4)
- GET /api/v1/tickets/{id}/download
- GET /api/v1/tickets/{id}/qr-code
- POST /api/v1/tickets/{id}/check-in
- POST /api/v1/tickets/{id}/refund

### Ticket Types (5)
- GET /api/v1/ticket-types
- POST /api/v1/ticket-types
- GET /api/v1/ticket-types/{id}
- PUT /api/v1/ticket-types/{id}
- DELETE /api/v1/ticket-types/{id}

### Users (5)
- GET /api/v1/users
- POST /api/v1/users
- GET /api/v1/users/{id}
- PUT /api/v1/users/{id}
- DELETE /api/v1/users/{id}

### Venues (5)
- GET /api/v1/venues
- POST /api/v1/venues
- GET /api/v1/venues/{id}
- PUT /api/v1/venues/{id}
- DELETE /api/v1/venues/{id}

### Withdrawals (3)
- GET /api/v1/withdrawals
- POST /api/v1/withdrawals
- GET /api/v1/withdrawals/{id}

**TOTAL: 84 Endpoints** ✅

---

## 🚀 Automatisation Complète

### Option 1: Utiliser Newman (Postman CLI)

```bash
# Installer Newman
npm install -g newman

# Tester toute la collection
newman run public/docs/collection.json

# Avec environnement
newman run public/docs/collection.json -e environment.json
```

### Option 2: Créer un Script Custom

Créer un script qui:
1. Lit `public/docs/collection.json`
2. Exécute chaque requête
3. Valide les réponses
4. Génère un rapport

**Exemple:** `scripts/test-all-endpoints.ps1` (à créer si besoin)

---

## 📝 Notes Importantes

1. **Collection Scribe vs Scénarios Critiques**
   - Scribe = TOUS les endpoints (82+)
   - Critiques = 20 endpoints essentiels

2. **Mise à Jour**
   - Collection Scribe: Auto-générée par `php artisan scribe:generate`
   - Scénarios critiques: Manuels, à maintenir

3. **Pour Production**
   - Tester avec collection Scribe AVANT déploiement
   - CI/CD: Utiliser scénarios critiques

---

## 🎯 Recommandation Finale

**Pour tester TOUS les 84 endpoints rapidement:**

1. **Importer** `public/docs/collection.json` dans Postman
2. **Configurer** un environnement avec vos variables
3. **Exécuter** la collection avec Collection Runner
4. **Vérifier** les résultats

**Temps estimé:** 10-15 minutes pour tout tester

---

**Créé le:** 29 juin 2026  
**Dernière MAJ:** 29 juin 2026  
**Status:** ✅ Complet
