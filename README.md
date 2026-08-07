# 🎫 TicketExpress Backend API

**Version:** 1.0.0  
**Status:** ✅ Production Ready  
**Framework:** Laravel 12  
**Database:** MySQL 5.7+

---

## 📋 Table of Contents

- [About](#about)
- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Status](#project-status)
- [Quick Start](#quick-start)
- [API Documentation](#api-documentation)
- [Testing](#testing)
- [Database](#database)
- [Queue Workers](#queue-workers)
- [Scheduled Tasks](#scheduled-tasks)
- [Monitoring](#monitoring)
- [Project Structure](#project-structure)
- [Development Guidelines](#development-guidelines)
- [Contributing](#contributing)

---

## 🎯 About

TicketExpress is a comprehensive ticket management platform for event organizers. The backend API provides a robust foundation for creating, managing, and selling event tickets with features like QR code check-ins, multi-date events, promotional pricing, and secure payment processing.

### Key Capabilities
- 🎟️ **Event Management** - Create and manage events with rich details
- 📅 **Multi-Date Events** - Support for recurring or multiple occurrence events
- 💳 **Order Processing** - Secure order management with payment integration
- 🔒 **QR Check-In System** - Physical and online event access verification
- 💰 **Promotions** - Temporal pricing and coupon systems
- 📊 **Analytics** - Organizer dashboard with revenue tracking
- 📧 **Email System** - Automated notifications and ticket delivery
- 🔐 **RBAC** - Role-based access control (Super Admin, Admin, Organizer, Client)

---

## ✨ Features

### Core Features
- ✅ **Authentication** - OTP-based login, registration, password reset
- ✅ **Event Management** - CRUD, publish/unpublish, cancellation
- ✅ **Ticket Types** - Rich descriptions, benefits, location details
- ✅ **Orders** - Complete order lifecycle with timestamps
- ✅ **Payments** - Secure payment processing
- ✅ **Check-Ins** - QR code scanning for physical/online events
- ✅ **Reviews** - Rating and review system for events
- ✅ **Withdrawals** - Organizer payment withdrawal requests
- ✅ **Coupons** - Discount and promotional codes
- ✅ **Notifications** - In-app notification system

### Advanced Features
- ✅ **Feature 3:** QR Check-In System (physical + online)
- ✅ **Feature 4:** Temporal Promotions (price reductions with dates)
- ✅ **Feature 5:** Multi-Date Events (EventOccurrence model)
- ✅ **Feature 6:** Availability Status (6 automatic statuses)
- ✅ **Feature 7:** Rich Ticket Descriptions (benefits, featured badges)

### Email Notifications (13 Mailables)
- Ticket confirmation with PDF + QR code
- Password reset
- Organizer approval/rejection
- Event cancellation
- Order confirmations
- And more...

---

## 🛠️ Tech Stack

### Backend
- **Framework:** Laravel 12
- **PHP:** 8.2.12
- **Database:** MySQL 5.7+
- **Authentication:** Laravel Sanctum
- **Queue:** Database driver
- **Cache:** Database driver

### Key Packages
- `spatie/laravel-permission` - Role & permission management
- `spatie/laravel-medialibrary` - Media uploads (logos, banners)
- `laravel/pulse` - Application monitoring
- `knuckleswtf/scribe` - API documentation generator
- `barryvdh/laravel-ide-helper` - IDE autocompletion

### External Integrations
- **OneSignal** - Push notifications
- **EdoKing SMS** - SMS/OTP delivery
- **Pusher** - WebSocket real-time features
- **Google Maps** - Location services

---

## 📊 Project Status

### Quality Metrics
| Metric | Status | Details |
|--------|--------|---------|
| **API Coverage** | ✅ 100% | 84/84 endpoints tested |
| **PHPStan** | ✅ 0 errors | Level 5 analysis |
| **Tests** | ✅ 242/242 passing | 100% success rate |
| **Laravel Pint** | ✅ Passing | PSR-12 compliant |
| **Migrations** | ✅ 26 consolidated | Optimized structure |

### Project Cleanup
- ✅ Root directory: 19 essential files (was 188)
- ✅ Documentation organized in `docs/`
- ✅ Scripts centralized in `scripts/`
- ✅ Professional structure

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 5.7+
- Node.js & npm (for asset compilation)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd TicketExpress-backend
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
copy .env.example .env
php artisan key:generate
```

4. **Configure database**
Edit `.env` file:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_express_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Run migrations & seeders**
```bash
php artisan migrate:fresh --seed
```

6. **Generate IDE helpers**
```bash
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
```

7. **Start development server**
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

---

## 📚 API Documentation

### Access Documentation
- **Interactive Docs:** `http://localhost:8000/docs`
- **Postman Collection:** `public/docs/collection.json`
- **OpenAPI Spec:** `public/docs/openapi.yaml`

### Generate Documentation
```bash
php artisan scribe:generate
```

### API Endpoints Overview
- **Auth:** 8 endpoints (login, register, OTP, password reset)
- **Events:** 15 endpoints (CRUD, publish, multi-dates)
- **Orders:** 4 endpoints (create, list, details, cancel)
- **Tickets:** 4 endpoints (download, QR, check-in, refund)
- **Organizers:** 5 endpoints (CRUD, approval, stats)
- **Payments:** 5 endpoints (withdrawals, processing)
- **Others:** 43 endpoints (coupons, reviews, venues, etc.)

**Total:** 84 fully tested endpoints

---

## 🧪 Testing

### Run All Tests
```bash
php artisan test --compact
```

### Run Specific Test File
```bash
php artisan test --compact tests/Feature/EventTest.php
```

### Run with Filter
```bash
php artisan test --compact --filter=testCreateEvent
```

### Test Coverage
- **Feature Tests:** Comprehensive API endpoint tests
- **Unit Tests:** Business logic validation
- **Success Rate:** 100% (242/242 passing)

### Test Users (Seeded)
```bash
php artisan db:seed --class=TestUsersSeeder
```

**Available test accounts:**
- Super Admin: `superadmin@ticketexpress.tg` / `password`
- Admin: `admin@ticketexpress.tg` / `password`
- Client: `komi.creppy@client.tg` / `password`
- Organizer: `manager@org.tg` / `password`

**OTP Bypass:** Use code `000000` in local environment

---

## 🗄️ Database

### Migrations
The project uses **26 consolidated migrations** (optimized from 41).

### Fresh Migration
```bash
php artisan migrate:fresh --seed
```

### Migration Status
```bash
php artisan migrate:status
```

### Database Structure
- **26 tables** including events, tickets, orders, payments
- **80+ optimized indexes** (10/10 performance score)
- **ULID primary keys** for all models
- **Foreign key constraints** properly managed

### Seeders
```bash
php artisan db:seed --class=TestUsersSeeder      # Test users
php artisan db:seed --class=TicketTestSeeder     # Sample tickets
php artisan db:seed --class=WithdrawalTestSeeder # Sample withdrawals
```

---

## ⚙️ Queue Workers

### Start Queue Workers (Windows)
```powershell
.\scripts\start-worker.ps1
```

This starts 3 priority queues:
1. **emails** (priority 10) - Email delivery
2. **notifications** (priority 5) - Push notifications
3. **default** (priority 1) - Background jobs

### Manual Start
```bash
php artisan queue:work --queue=emails,notifications,default
```

### Queue Jobs
- `SendEmailJob` - Handles all email sending
- Event listeners automatically queue notifications
- Background processing for heavy operations

---

## ⏰ Scheduled Tasks

Scheduled commands are declared in `App\Bootstrappers\ScheduleBootstrapper`
(wired from `bootstrap/app.php`).

**Development**
```bash
php artisan schedule:work
```

**Production** — one cron entry, running every minute:
```cron
* * * * * cd /path/to/TicketExpress-backend && php artisan schedule:run >> /dev/null 2>&1
```

### `tickets:expire` (hourly)

Marks as `expired` every `valid`, never-scanned ticket whose event's check-in
window has closed. Without it, a ticket for a finished event keeps showing as
"Valide" in the back office and in the holder's account.

```bash
php artisan tickets:expire --dry-run   # count only, change nothing
php artisan tickets:expire
```

Tickets that were actually used, refunded or cancelled are left untouched —
those statuses carry a decision the expiry must not overwrite.

### Check-in window

A ticket can only be validated between `checkin_open_hours_before` before its
event starts and `checkin_close_hours_after` after it ends. Outside that window
the gate refuses the ticket **without consuming it**.

The margins belong to the organizer, never to the platform: they are the ones
holding the gate. They are resolved in three tiers, most specific first:

1. **the event** — `events.checkin_open_hours_before` /
   `checkin_close_hours_after`, filled in at creation only when *this* event is
   an exception (**Événements → (modifier) → Contrôle d'accès**).
2. **the organizer** — the same two columns on `organizers`, their usual way of
   working, set once and followed by everything they schedule. Organizers set
   it themselves under **Administration → Contrôle d'accès**, which talks to
   `GET|PUT /api/v1/organizers/me` — an endpoint restricted to those two fields
   so an organizer cannot approve themselves through it. Admins can also set it
   from **Organisateurs → (modifier)**.
3. **`config/ticketexpress.php`** — factory values, 4 h either side.

Both sets of columns are nullable, and `null` means *inherit* — distinct from
`0`, which means "do not open a minute early". An event that says nothing keeps
following its organizer, including when that organizer changes their habit
later. The super-admin does not set this anywhere.

The rule lives in `App\Support\CheckInWindow` and is enforced by the gate
(`ValidateEventTicketAction`), by manual back-office validation
(`CheckInTicketAction`) and by `tickets:expire` alike.

### Events running on several nights

When a ticket type names an occurrence (`ticket_types.occurrence_id`), the
window is computed on **that occurrence's** dates rather than on the event's.
Without it, an event held on the 7th and the 9th has a window spanning both, so
a ticket sold for the 9th would be accepted — and burnt — on the 7th, leaving
its bearer turned away on the night they paid for. A ticket with no occurrence
keeps following the event, which is the case for most of the catalogue.

The margins still come from the event, then the organizer: an occurrence carries
a date, not a way of opening the doors. The check-in panel's banner stays at
event scope — it announces the gate, not one night; each scan's verdict is what
decides.

---

## 📈 Monitoring

### Laravel Pulse Dashboard
Access real-time monitoring at `http://localhost:8000/pulse`

**Monitors:**
- Slow queries
- Slow requests
- Exceptions
- Queue jobs
- Cache interactions
- Server metrics

### Logs
```bash
tail -f storage/logs/laravel.log
```

---

## 📁 Project Structure

```
TicketExpress-backend/
├── app/
│   ├── Actions/              # Business logic (organized by domain)
│   ├── Contracts/            # Interfaces
│   ├── Events/               # Domain events
│   ├── Http/
│   │   ├── Controllers/      # Thin controllers
│   │   ├── Requests/         # Form validation
│   │   ├── Resources/        # API responses
│   │   └── Middleware/       # Custom middleware
│   ├── Jobs/                 # Queue jobs
│   ├── Listeners/            # Event listeners
│   ├── Mail/                 # Mailable classes (13)
│   ├── Models/               # Eloquent models (20+)
│   ├── Notifications/        # Notification classes
│   └── Policies/             # Authorization policies
├── bootstrap/                # App bootstrapping
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # 26 consolidated migrations
│   └── seeders/              # Database seeders
├── docs/
│   ├── reports/              # Important reports (5 key documents)
│   └── archive/              # Historical documentation
├── public/
│   └── docs/                 # Generated API documentation
├── resources/
│   ├── views/                # Blade templates (emails)
│   └── lang/                 # Translations
├── routes/
│   ├── api.php               # API routes entry
│   └── api/v1/               # Versioned API routes
├── scripts/                  # Utility scripts
│   ├── start-worker.ps1      # Queue worker starter
│   └── sync-postman.ps1      # Postman sync
├── storage/
│   ├── app/                  # Application storage
│   ├── logs/                 # Log files
│   └── framework/            # Framework files
├── tests/
│   ├── Feature/              # API endpoint tests
│   └── Unit/                 # Unit tests
├── .env.example              # Environment template
├── composer.json             # PHP dependencies
├── phpunit.xml               # PHPUnit configuration
└── README.md                 # This file
```

---

## 📖 Development Guidelines

### Code Style
- **PSR-12** compliant (enforced by Laravel Pint)
- **Strict typing** everywhere (`declare(strict_types=1);`)
- **PHPDoc** for array shapes and complex types
- **Type hints** for all parameters and returns

### Architecture Patterns
- **Actions pattern:** Business logic in `app/Actions/V1/`
- **Thin controllers:** Controllers only handle HTTP
- **Form Requests:** All validation in dedicated classes
- **API Resources:** Never return raw models
- **Policies:** All authorization logic centralized
- **ULID:** Use ULIDs for all model IDs

### Before Committing
```bash
# Format code
vendor/bin/pint

# Check types
vendor/bin/phpstan analyse

# Run tests
php artisan test --compact
```

Or use the combined script:
```bash
composer clean
```

### Creating New Features
1. Create migration (if needed)
2. Create/update model
3. Create Action class for business logic
4. Create Form Request for validation
5. Create Resource for API output
6. Create thin Controller
7. Add route
8. Create/update Policy
9. Add Scribe annotations
10. Write tests

### Naming Conventions
- **Actions:** `VerbNounAction` (e.g., `CreateEventAction`)
- **Requests:** `VerbNounRequest` (e.g., `StoreEventRequest`)
- **Resources:** `NounResource` (e.g., `EventResource`)
- **Controllers:** `NounController` (e.g., `EventController`)
- **Policies:** `NounPolicy` (e.g., `EventPolicy`)

---

## 🤝 Contributing

### Development Workflow
1. Create a feature branch
2. Follow coding conventions
3. Write/update tests
4. Run quality checks (`composer clean`)
5. Commit with descriptive message
6. Push and create Pull Request

### Quality Requirements
- ✅ All tests must pass (242/242)
- ✅ PHPStan must pass with 0 errors
- ✅ Laravel Pint must pass
- ✅ New features must include tests
- ✅ API changes must update Scribe docs

### AI Assistant Context
See `AGENTS.md` for AI assistant guidelines and project context for Kiro, Claude, or other AI tools.

---

## 📝 Key Documentation Files

### Essential Reading
- **`AGENTS.md`** - AI assistant guidelines (Laravel Boost rules)
- **`.kiro/steering/project-context.md`** - Current project state
- **`.kiro/steering/laravel-vue-conventions.md`** - Code conventions

### Reports (in `docs/reports/`)
- **`100_PERCENT_COVERAGE_ATTEINT.md`** - 100% API coverage milestone
- **`MIGRATION_COMPLETE_CLEANUP.md`** - Migration consolidation
- **`CLEANUP_FINAL_SUMMARY.md`** - Project cleanup summary
- **`FIX_PULSE_CACHE_GROUPS.md`** - Laravel Pulse configuration fix

---

## 🔐 Security

### Best Practices
- ✅ Laravel Sanctum for API authentication
- ✅ CORS configured properly
- ✅ Rate limiting on sensitive endpoints
- ✅ Input validation on all requests
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)
- ✅ CSRF protection on web routes

### Reporting Vulnerabilities
If you discover a security vulnerability, please email the development team immediately.

---

## 📄 License

This project is proprietary software. All rights reserved.

---

## 👥 Team

**Developed by:** ZESGIS Team  
**AI Assistant:** Kiro AI  
**Framework:** Laravel 12  
**Last Updated:** June 29, 2026

---

## 🎉 Achievements

- ✅ **100% API Coverage** (84/84 endpoints)
- ✅ **0 PHPStan Errors** (cleaned from 224)
- ✅ **242/242 Tests Passing**
- ✅ **26 Optimized Migrations** (from 41)
- ✅ **19 Root Files** (from 188, -89.9%)
- ✅ **Production Ready**

---

**Ready to power amazing events! 🎫🚀**
