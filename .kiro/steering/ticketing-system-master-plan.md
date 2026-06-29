---
inclusion: manual
---

# TicketExpress - Master Plan for Full Ticketing System

## SYSTEM OVERVIEW

TicketExpress is a complete event ticketing platform supporting:
- **Guest checkout** (purchase without account)
- **Optional account creation**
- **Automatic email-based linking**
- **Separate attendees from users**

## DATABASE SCHEMA

### 1. users (existing - needs minimal updates)
Represents user accounts (organizer or registered customer).

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| first_name | string | |
| last_name | string | |
| email | string | unique |
| phone | string | unique |
| password | string | |
| role | enum | admin, organizer, user |
| email_verified_at | timestamp | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 2. organizers
Organizer profiles.

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| user_id | foreign key | → users |
| company_name | string | |
| description | text | |
| logo | string | nullable |
| website | string | nullable |
| status | enum | pending, approved, rejected |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3. event_categories

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| name | string | |
| slug | string | unique |
| created_at | timestamp | |
| updated_at | timestamp | |

### 4. venues

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| name | string | |
| address | text | |
| city | string | |
| country | string | |
| capacity | integer | |
| latitude | decimal | nullable |
| longitude | decimal | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 5. events

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| organizer_id | foreign key | → organizers |
| category_id | foreign key | → event_categories |
| venue_id | foreign key | → venues (nullable) |
| title | string | |
| slug | string | unique |
| description | longText | |
| banner | string | nullable |
| start_date | datetime | |
| end_date | datetime | |
| max_attendees | integer | nullable |
| status | enum | draft, published, cancelled, finished |
| created_at | timestamp | |
| updated_at | timestamp | |

### 6. ticket_types

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| event_id | foreign key | → events |
| name | string | VIP, Standard, etc. |
| description | text | nullable |
| price | decimal | |
| quantity | integer | |
| sold_quantity | integer | default 0 |
| sale_start_date | datetime | nullable |
| sale_end_date | datetime | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 7. orders (PURCHASE)
Represents a purchase (with or without account).

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| user_id | foreign key | → users (nullable) |
| first_name | string | |
| last_name | string | |
| email | string | |
| phone | string | |
| total_amount | decimal | |
| status | enum | pending, paid, cancelled, refunded |
| payment_method | string | nullable |
| delivery_method | enum | email, whatsapp, both |
| created_at | timestamp | |
| updated_at | timestamp | |

### 8. order_items

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| order_id | foreign key | → orders |
| ticket_type_id | foreign key | → ticket_types |
| quantity | integer | |
| unit_price | decimal | |
| subtotal | decimal | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 9. tickets
Individual tickets generated after payment.

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| order_id | foreign key | → orders |
| ticket_type_id | foreign key | → ticket_types |
| attendee_name | string | |
| attendee_email | string | |
| qr_code | string | unique |
| ticket_number | string | unique |
| status | enum | valid, used, cancelled |
| checked_in_at | datetime | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 10. payments

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| order_id | foreign key | → orders |
| amount | decimal | |
| method | enum | stripe, wave, flooz, tmoney, paypal |
| transaction_reference | string | |
| status | enum | pending, success, failed |
| paid_at | datetime | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 11. check_ins
Ticket scanning at entrance.

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| ticket_id | foreign key | → tickets |
| scanned_by | foreign key | → users |
| scanned_at | datetime | |
| device_info | string | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 12. coupons

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| code | string | unique |
| type | enum | percent, fixed |
| value | decimal | |
| max_usage | integer | |
| used_count | integer | default 0 |
| start_date | datetime | |
| end_date | datetime | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 13. event_coupon (pivot)

| Field | Type | Notes |
|-------|------|-------|
| event_id | foreign key | → events |
| coupon_id | foreign key | → coupons |

### 14. notifications (existing)

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| user_id | foreign key | → users |
| title | string | |
| message | text | |
| read_at | datetime | nullable |
| created_at | timestamp | |
| updated_at | timestamp | |

### 15. withdrawals

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| organizer_id | foreign key | → organizers |
| amount | decimal | |
| status | enum | pending, approved, rejected, paid |
| payment_method | string | |
| created_at | timestamp | |
| updated_at | timestamp | |

### 16. reviews

| Field | Type | Notes |
|-------|------|-------|
| id | ULID | Primary key |
| event_id | foreign key | → events |
| user_id | foreign key | → users (nullable) |
| rating | integer | 1-5 |
| comment | text | |
| created_at | timestamp | |
| updated_at | timestamp | |

## CRITICAL BUSINESS LOGIC

### 🔥 Key Rules:

1. **user_id is optional in orders** → only if account exists
2. **email is the business key** → links all purchases
3. **tickets contain real attendees** → separate from buyer
4. **QR codes must be unique** → for check-in
5. **sold_quantity must be atomic** → prevent overselling

## IMPLEMENTATION STRATEGY

### Phase 1: Core Domain (Organizers, Events, Venues)
- Organizers (CRUD)
- Event Categories (CRUD)
- Venues (CRUD)
- Events (CRUD with relationships)
- Ticket Types (CRUD nested under events)

### Phase 2: Order & Payment System
- Guest checkout flow
- Order creation
- Payment integration (Stripe, Wave, Flooz, Tmoney)
- Ticket generation after payment
- QR code generation

### Phase 3: Coupon & Discount System
- Coupon CRUD
- Event-coupon linking
- Discount calculation
- Usage tracking

### Phase 4: Check-in System
- QR scanning
- Ticket validation
- Check-in tracking
- Real-time status updates

### Phase 5: Reviews & Withdrawals
- Event reviews
- Organizer withdrawal requests
- Payment processing

## CODE GENERATION RULES

### 1. Follow Existing Patterns:
- ULID for all IDs (`HasUlids`)
- Actions for business logic (`Action` contract)
- Form Requests for validation (array rules)
- Resources for responses (camelCase)
- Strict typing everywhere
- Final classes for controllers/requests/resources

### 2. Auto-Generate:
- Models with relationships
- Migrations with indexes
- Controllers (thin layer)
- Form Requests (with Scribe bodyParameters)
- Resources (camelCase keys)
- Actions (with contracts if needed)
- Routes (in routes/api/v1/)
- Tests (PHPUnit feature tests)
- Seeders (realistic data)

### 3. MCP Integration:
- Use `laravel-boost` for Artisan commands
- Use `mysql` to verify schema
- Use `git` for version control
- Use `memory` to track progress
- Use `fetch` to test APIs

### 4. Postman Collections:
- Generate collections per domain
- Include auth tokens
- Test scripts with pm.test
- Success & error scenarios
- Environment variables

## TEAMMATE ASSIGNMENTS

### TEAMMATE 0 — Codebase Consistency
- ✅ Analysis completed
- Monitor all generated code for consistency

### TEAMMATE 1 — Domain Architect
- Design enums (OrganizerStatus, EventStatus, OrderStatus, PaymentStatus, TicketStatus, DeliveryMethod, PaymentMethod, CouponType, WithdrawalStatus)
- Define relationships
- Business rules validation

### TEAMMATE 2 — Database Engineer
- 16 migrations to create
- Foreign keys with cascades
- Indexes on: email, phone, slug, qr_code, ticket_number, status fields
- Seeders with realistic data

### TEAMMATE 3 — Backend API Developer
- Controllers for all domains
- Actions for complex logic
- Route registration
- API versioning

### TEAMMATE 4 — Auth Specialist
- Verify existing auth still works
- Add organizer role checks
- Check-in permissions

### TEAMMATE 5 — API Resource Engineer
- Resources for all models
- Pagination for listings
- Filters & search
- API documentation (Scribe)
- Postman collection generation

### TEAMMATE 6 — Test Engineer
- Feature tests for all endpoints
- Test guest checkout flow
- Test account linking
- Test payment flow
- Test check-in flow

### TEAMMATE 7 — QA Engineer
- End-to-end scenarios
- Guest purchase → receive ticket
- Account creation → link past orders
- Coupon validation
- QR code uniqueness
- Overselling prevention

### TEAMMATE 8 — DevOps & Final Validation
- Final coherence check
- API completeness
- Postman collection validation
- FINAL_TICKETING_SYSTEM_REPORT.md

## SUCCESS CRITERIA

- ✅ All 16 tables created and seeded
- ✅ All CRUD endpoints working
- ✅ Guest checkout functional
- ✅ Payment integration ready
- ✅ QR code generation working
- ✅ Check-in system operational
- ✅ Postman collections complete
- ✅ Tests passing
- ✅ Production-ready code
