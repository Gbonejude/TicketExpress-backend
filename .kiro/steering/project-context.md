# TicketExpress Project Context

This file maintains the current state, architecture, and context of the TicketExpress platform. It serves as a memory for AI assistants to understand the project without getting lost.

## Project Overview

**Name:** TicketExpress  
**Type:** Ticket management platform (evolved from car wash system)  
**Stack:** Laravel 12 (Backend) + Vue 3/Nuxt (Frontend)  
**Database:** MySQL (`ticket_express_db`)  
**Auth:** Laravel Sanctum with OTP flow

## Architecture Decisions

### Backend Structure
- **Actions pattern:** Business logic in `app/Actions/V1/` organized by domain
- **Contracts/Interfaces:** All services behind contracts in `app/Contracts/`
- **Resources:** API responses via `app/Http/Resources/` (never raw models)
- **Validation:** Form Requests in `app/Http/Resources/` (never inline)
- **IDs:** Using ULIDs (`HasUlids` trait), not auto-increment
- **API Versioning:** Routes organized under `/api/v1/`

### Key Domains Implemented

#### 1. Authentication (Completed)
- **Location:** `app/Actions/V1/Auth/`
- **Features:**
  - User registration (`RegisterUserAction`)
  - Admin login (`AdminLoginAction`)
  - OTP-based authentication (`SendOtpAction`, `VerifyOtpAction`)
  - Token management (`IssueTokenAction`)
  - Password reset flow (`ForgotPasswordAction`, `ResetPasswordAction`)
  - Logout (`LogoutAction`)
- **Provider:** Sanctum

#### 2. Organizations (Completed)
- **Location:** `app/Actions/V1/Organization/`
- **Features:**
  - CRUD operations (Create, Update, Delete)
- **Model:** Organization with ULID

#### 3. License Plates (Completed)
- **Location:** `app/Actions/V1/LicensePlate/`
- **Features:**
  - CRUD operations (Create, Update, Delete)
- **Model:** LicensePlate with ULID

#### 4. Services (Completed)
- **Location:** `app/Actions/V1/Service/`
- **Features:**
  - CRUD operations (Create, Delete)
- **Model:** Service with ULID

#### 5. Invoices (Completed)
- **Location:** `app/Actions/V1/Invoice/`
- **Features:**
  - Create and Delete invoices
- **Model:** Invoice with ULID

## Database Schema

**Current Database:** `ticket_express_db`  
**Migrations:** 26 fichiers consolidés (vs 41 avant)  
**Status:** ✅ Structure optimisée et production-ready

### Key Tables
- `users` - User accounts (ULID primary key) + revoked_screens
- `otp_codes` - OTP verification codes
- `roles` - Roles with back-office flags (Spatie Permission)
- `permissions` - Permissions system (Spatie Permission)
- `organizers` - Event organizers (ULID) + rejection_reason
- `events` - Events (ULID) + published_at, cancelled_at
- `event_categories` - Event categories (ULID)
- `event_occurrences` - Multi-date events (ULID)
- `venues` - Event venues (ULID)
- `ticket_types` - Ticket types (ULID) + rich descriptions, promotions, occurrence_id
- `tickets` - Individual tickets (ULID) + refund fields, check-in fields, access_method
- `orders` - Orders (ULID) + order_number, 4 timestamps (paid_at, confirmed_at, cancelled_at, refunded_at)
- `order_items` - Order line items
- `payments` - Payment records (ULID)
- `coupons` - Discount coupons (ULID)
- `reviews` - Event reviews (ULID)
- `withdrawals` - Organizer withdrawals (ULID)
- `check_ins` - Ticket check-ins
- `ticket_download_links` - Secure download links
- `favorite_events` - User favorites
- `notifications` - User notifications
- `pulse_*` - Laravel Pulse monitoring tables
- `personal_access_tokens` - Sanctum tokens
- `media` - Spatie Media Library (logos, banners)
- `sessions` - Session management (database driver)
- `cache` - Cache storage (database driver)
- `jobs` - Queue jobs (database driver)

## External Integrations

### OneSignal (Push Notifications)
- **App ID:** Configured in `.env`
- **Usage:** Mobile push notifications

### EdoKing SMS
- **Provider:** Kings MS Pro
- **Usage:** SMS/OTP delivery
- **Config:** Client ID, API Key in `.env`

### Pusher (WebSockets)
- **Cluster:** mt1
- **Usage:** Real-time features

### Google Maps
- **API Key:** Configured in `.env`
- **Usage:** Location services

## API Documentation

**Tool:** Scribe  
**Location:** `public/docs/` directory  
**Endpoints documented:** 82 endpoints complets (Auth, Organizations, Services, Events, Orders, Tickets, etc.)  
**Access:** `http://localhost:8000/docs`  
**Formats:** HTML, Postman Collection, OpenAPI/Swagger  
**Status:** ✅ Généré sans erreurs (EventCategoryFactory corrigé avec slugs uniques)

## Development Environment

### MCP Servers Configured
1. `laravel-boost` - Artisan, Tinker, Laravel docs
2. `fetch` - API testing (Postman-like)
3. `filesystem` - Advanced file operations
4. `git` - Version control
5. `sequential-thinking` - Complex reasoning
6. `mysql` - Direct database access
7. `memory` - Persistent notes
8. `github` - GitHub integration
9. `duckduckgo` - Web search

### Running Services
- **PHP:** 8.2.12
- **Laravel:** 12
- **Database:** MySQL 5.7+
- **Queue:** Database driver
- **Cache:** Database driver
- **Session:** Database driver

## Conventions Applied

See `laravel-vue-conventions.md` for detailed conventions.

**Key principles:**
- Strict typing (`declare(strict_types=1);`)
- Production-ready code only
- ULIDs for all model IDs
- Resources for all API responses
- Form Requests for validation
- Actions for business logic
- Policies for authorization

## Current Status

### ✅ Completed
- Authentication system with OTP
- User management with role-based access control (RBAC)
- Event management (CRUD, publish/unpublish, cancel)
- Multi-date events (EventOccurrence model)
- Ticket types with rich descriptions & promotions
- Order management with timestamps & order numbers
- Payment & withdrawal system
- Check-in system (QR codes, physical + online)
- Review & rating system
- Coupon/discount system
- Favorite events
- Notification system (in-app)
- **Laravel Pulse monitoring (dashboard accessible at `/pulse`) ✅**
- **Database migrations consolidation (41 → 26 files, -36.6%) ✅**
- **Project cleanup (188 → 19 root files, -89.9%) ✅**
- **Database indexes optimization (80+ indexes, 10/10 score) ✅**
- **Unit & functional tests (242/242 passing, 100% success rate) ✅**
- **PHPStan cleanup complete (224 → 0 errors, -100%, production-ready) ✅**
- **API Coverage: 100% (84/84 endpoints tested and working) ✅✅✅**
- **Spatie Media Library (Organizer logo, Event banner) ✅**
- **CLIENT registration (with password choice) ✅**
- **ORGANIZER_MANAGER registration (with password choice) ✅**
- **Priority emails (5/5: ticket confirmation with PDF+QR, password reset, organizer approval/rejection, event cancellation) ✅**
- **WhatsApp link generation (wa.me with pre-filled messages) ✅**
- **Email queues configured (emails, notifications) with SendEmailJob ✅**
- **Refund policy system (voluntary + automatic on event cancellation) ✅**
- **Order timestamps tracking (paid_at, confirmed_at, cancelled_at, refunded_at) ✅**
- **Order number system (7-digit unique numbers, auto-generated) ✅**
- **International phone validation (E.164 format for clients, Togo-only for organizers) ✅**
- **Delivery method system (email, whatsapp, both) ✅**
- **Feature 6: Availability status system (6 automatic statuses based on stock levels) ✅**
- **Feature 7: Rich ticket descriptions (benefits, location details, featured badges, sort order) ✅**
- **Feature 5: Multi-date events (EventOccurrence model complete with API) ✅**
- **Feature 3: Check-in QR system (physical + online events) ✅**
- **Feature 4: Temporal promotions (price reductions with start/end dates) ✅**
- **Composer clean script (Pint + PHPStan + Tests) ✅**
- **Email Queue System (SendEmailJob + 13 Mailables + 9 Listeners + Test Command) ✅**
- **Notification System (4 notifications + NotificationHelper) ✅**
- **Worker scripts (PowerShell + Batch with 3 priority queues) ✅**
- **Scribe documentation (82 endpoints, EventCategoryFactory fixed) ✅**
- **Test Users Seeder (super-admin, admin, client Komi CREPPY, organizer-manager) ✅**
- **OTP Bypass for testing (code 000000 in local environment) ✅**

### 🚧 In Progress
- Aucune tâche en cours

### 📋 Planned
- **Feature 2: Ticket transfers** (si demandé par client, estimation: 3-4h)
- **Tests E2E** avec Playwright ou Cypress (optionnel)
- **Déploiement staging** pour tests utilisateurs réels

## Project Structure

### Root Directory (19 files) ✨
Optimisé pour maintenabilité et professionnalisme:

- **Configuration:** `.env`, `.env.example`, `.gitignore`, `.mcp.json`, `boost.json`, `phpunit.xml`
- **Documentation:** `README.md`, `AGENTS.md`
- **IDE Support:** `_ide_helper.php`, `_ide_helper_models.php`
- **Build:** `composer.json`, `package.json`, `vite.config.js`, `artisan`

### Organized Folders
- **`docs/reports/`** - Rapports clés (100% coverage, migrations cleanup, etc.)
- **`docs/archive/`** - Historique et anciens rapports
- **`scripts/`** - Scripts utiles (workers, Postman sync, etc.)
- **`app/`** - Code application Laravel
- **`database/`** - Migrations (26 consolidées), Seeders, Factories
- **`tests/`** - Tests unitaires & fonctionnels (242 passing)

## Notes for AI Assistants

- Always check this file before starting new features
- Update this file when completing major features
- Respect the established architecture patterns
- Never bypass Actions/Resources/Form Requests
- All new models should use ULID
- Communicate in French, code in English

## Last Updated

**Date:** 2026-06-29 15:30 UTC  
**By:** Kiro AI  
**Status:** ✅ **PRODUCTION READY**

### Recent Accomplishments
- ✅ **100% API Coverage** (84/84 endpoints testés et fonctionnels)
- ✅ **0 PHPStan Errors** (vs 224 avant, -100%)
- ✅ **242/242 Tests Passing** (100% success rate)
- ✅ **Migrations Consolidation** (41 → 26 files, -36.6%)
- ✅ **Project Cleanup** (188 → 19 root files, -89.9%)
- ✅ **Documentation complète** dans `docs/reports/`

### Key Reports
- `docs/reports/100_PERCENT_COVERAGE_ATTEINT.md` - Milestone 100% API coverage
- `docs/reports/MIGRATION_COMPLETE_CLEANUP.md` - Consolidation migrations
- `docs/reports/CLEANUP_FINAL_SUMMARY.md` - Résumé complet du nettoyage
- `docs/reports/FIX_PULSE_CACHE_GROUPS.md` - Fix Laravel Pulse

**Next Steps:** Déploiement staging recommandé pour tests utilisateurs réels
