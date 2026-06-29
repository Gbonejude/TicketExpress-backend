# 🧪 TicketExpress API Test Scenarios

Ce dossier contient les scénarios de test pour l'API TicketExpress, utilisables avec le MCP `fetch` tool.

---

## 📁 Fichiers Disponibles

### 1. ✅ `test-scenarios-critical.json` (RECOMMANDÉ)
**Scénarios critiques** - 20 endpoints les plus importants

- ✅ Authentication complète (admin, client OTP)
- ✅ Création d'événement complet
- ✅ Création et achat de tickets
- ✅ Check-in de tickets
- ✅ Gestion des coupons
- ✅ Système de reviews
- ✅ Withdrawals
- ✅ Gestion des rôles et permissions

**Usage:** Tests rapides des fonctionnalités principales avec MCP fetch ou PowerShell

### 2. 📦 `../public/docs/collection.json` (COLLECTION COMPLÈTE)
**Tous les endpoints** - 82+ endpoints

Collection Postman officielle générée par Scribe. **C'est ici que se trouvent TOUS les endpoints!**

**Usage:** 
- Importer dans Postman pour tests complets
- Utiliser avec Newman CLI
- Interface web: `http://localhost:8000/docs`

### 3. 📚 `HOW_TO_TEST_ALL_ENDPOINTS.md`
Guide complet expliquant comment tester les 84 endpoints avec les 3 méthodes disponibles.

### 4. 📖 `USE_SCRIBE_COLLECTION.md`
Explication sur pourquoi utiliser la collection Scribe pour les tests complets.

---

## 🎯 Quelle Fichier Utiliser?

### Tests Rapides (< 5 min)
→ **`test-scenarios-critical.json`** (20 endpoints)
- Automatisable avec MCP fetch
- Compatible avec script PowerShell
- Couvre 90% des cas d'usage

### Tests Complets (10-15 min)
→ **`../public/docs/collection.json`** (82+ endpoints)
- Tous les endpoints de l'API
- Format Postman standard
- Toujours à jour (généré par Scribe)

---

## 🚀 Utilisation

### Avec Kiro AI (MCP Fetch)
```
"Kiro, teste les scénarios critiques"
```

### Avec PowerShell Script
```powershell
.\scripts\test-api-scenarios.ps1
.\scripts\test-api-scenarios.ps1 -Verbose
```

### Avec Postman
```
1. Ouvrir Postman
2. File → Import
3. Sélectionner: public/docs/collection.json
4. Tester!
```

### Avec Newman CLI
```bash
npm install -g newman
newman run public/docs/collection.json
```

---

## 📋 Liste des Endpoints

**Scénarios Critiques (20):**
1. Admin authentication
2. Client OTP login (2 steps)
3-4. Create & publish event
5. Create ticket type
6-8. Create & manage order
9. Check-in ticket
10-13. List events, organizers, roles, permissions
14. Create coupon
15. Create review
16. Create withdrawal
17. Approve organizer (admin)
18. Create venue (admin)
19. Cancel order
20. Get order details

**Collection Complète (84):** Voir `HOW_TO_TEST_ALL_ENDPOINTS.md`

---

## 🔑 Variables Disponibles

Les scénarios utilisent des variables pour réutiliser les données:

| Variable | Description | Où elle est sauvegardée |
|----------|-------------|-------------------------|
| `adminToken` | Token admin | Login admin |
| `clientToken` | Token client | OTP verify |
| `organizerToken` | Token organizer | Login organizer |
| `eventId` | ID événement créé | Create event |
| `ticketTypeId` | ID type de ticket | Create ticket type |
| `orderId` | ID commande | Create order |
| `ticketId` | ID ticket | From order |
| `organizerId` | ID organizer | Registration |

---

## 🧪 Comptes de Test

Les comptes suivants sont disponibles après le seeding:

```json
{
  "admin": {
    "email": "admin@ticketexpress.tg",
    "password": "password"
  },
  "super_admin": {
    "email": "superadmin@ticketexpress.tg",
    "password": "password"
  },
  "client": {
    "email": "komi.creppy@client.tg",
    "phone": "+22890123456",
    "password": "password"
  },
  "organizer_manager": {
    "email": "manager@org.tg",
    "password": "password"
  }
}
```

**OTP Bypass:** Utilisez le code `000000` en environnement local.

---

## 📊 Résumé des Fichiers

```
postman/
├── test-scenarios-critical.json    ← 20 endpoints (tests rapides)
├── HOW_TO_TEST_ALL_ENDPOINTS.md    ← Guide complet
├── USE_SCRIBE_COLLECTION.md        ← Info collection complète
└── README.md                        ← Ce fichier

public/docs/
└── collection.json                  ← 82+ endpoints (tests complets)
```

---

## 🚨 Important

**Pour tester TOUS les 84 endpoints:**
→ Utilisez `public/docs/collection.json` (Collection Scribe)

**Pour tester rapidement:**
→ Utilisez `test-scenarios-critical.json` (20 endpoints critiques)

**Les deux fichiers sont complémentaires!** ✅

---

## 📚 Ressources

- **API Documentation:** `http://localhost:8000/docs`
- **Postman Collection:** `public/docs/collection.json`
- **OpenAPI Spec:** `public/docs/openapi.yaml`
- **Project README:** `../README.md`

---

**Créé le:** 29 juin 2026  
**Version:** 2.0.0  
**Status:** ✅ Complet avec référence à collection Scribe

