# API Testing Session 1 - Results

**Date:** 2026-06-29 16:22  
**Session:** First automated API test using PowerShell scripts  
**Total Tests:** 12  
**Passed:** 5 (41.67%)  
**Failed:** 7 (58.33%)

## ✅ Tests Passed (5/12)

### Authentication
1. **Admin Login** - ✅ SUCCESS
   - Endpoint: `POST /api/v1/auth/admin/login`
   - Credentials: `admin@test.tg` / `Password123!`
   - Token obtained successfully

2. **Client OTP Send** - ✅ SUCCESS
   - Endpoint: `POST /api/v1/auth/send-otp`
   - Phone: `+22890510465`
   - OTP sent successfully (bypass code: `000000`)

3. **Client OTP Verify** - ✅ SUCCESS
   - Endpoint: `POST /api/v1/auth/verify-otp`
   - Token obtained successfully

4. **Organizer Login** - ✅ SUCCESS
   - Endpoint: `POST /api/v1/auth/admin/login`
   - Credentials: `organizer@test.tg` / `Password123!`
   - Token obtained successfully

### Admin Operations
5. **List Organizers** - ✅ SUCCESS
   - Endpoint: `GET /api/v1/organizers`
   - Authorization: Bearer token (admin)
   - Result: 1 organizer found

## ❌ Tests Failed (7/12)

### Setup Data
1. **Create Event Category** - ❌ FAILED (404)
   - Tested: `POST /api/v1/event-categories`
   - **Issue:** Wrong endpoint
   - **Fix:** Use `POST /api/v1/categories` instead

2. **Create Venue** - ❌ FAILED (403)
   - Endpoint: `POST /api/v1/venues`
   - **Issue:** Permission denied (admin token required)
   - **Cause:** Need to verify policy or middleware

### Event Management
3. **List Public Events** - ❌ FAILED
   - Endpoint: `GET /api/v1/events?per_page=20`
   - **Issue:** Unknown error (needs investigation)
   - **Expected:** Should work without authentication

### Order Management
4. **List My Orders** - ❌ FAILED
   - Endpoint: `GET /api/v1/orders`
   - Authorization: Bearer token (client)
   - **Issue:** Unknown error (needs investigation)

### Coupons
5. **Create Coupon** - ❌ FAILED (403)
   - Endpoint: `POST /api/v1/coupons`
   - Authorization: Bearer token (organizer)
   - **Issue:** Permission denied
   - **Cause:** Organizer may not have permission

### Admin Operations
6. **List Roles** - ❌ FAILED (404)
   - Tested: `GET /api/v1/roles`
   - **Issue:** Route does not exist
   - **Status:** No `/api/v1/roles` endpoint in routes
   - **Resolution:** Either create the route or remove from tests

7. **List Permissions** - ❌ FAILED (403)
   - Endpoint: `GET /api/v1/permissions`
   - Authorization: Bearer token (admin)
   - **Issue:** Permission denied
   - **Cause:** Need to verify policy

## Route Corrections Needed

### Event Categories
- ❌ **Wrong:** `/api/v1/event-categories`
- ✅ **Correct:** `/api/v1/categories`

### Roles
- **Status:** No role management endpoints in API v1
- **Available routes:** None found
- **Recommendation:** Either add role endpoints or remove from test suite

## Test User Accounts (from TestUsersSeeder)

```json
{
  "super_admin": {
    "email": "superadmin@test.tg",
    "password": "Password123!",
    "phone": "+22890999901",
    "role": "super-admin"
  },
  "admin": {
    "email": "admin@test.tg",
    "password": "Password123!",
    "phone": "+22890999902",
    "role": "admin"
  },
  "client": {
    "email": "judasgbone@gmail.com",
    "password": "Password123!",
    "phone": "+22890510465",
    "role": "client"
  },
  "organizer_manager": {
    "email": "organizer@test.tg",
    "password": "Password123!",
    "phone": "+22890999903",
    "role": "organizer-manager",
    "organizer_status": "approved"
  }
}
```

## Token Response Formats

### Admin/Organizer Login Response
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "accessToken": "01xxx|token...",
    "userData": {...},
    "userAbilityRules": [...]
  }
}
```

### OTP Verify Response
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "is_new_user": false,
    "token": "01xxx|token...",
    "user": {...}
  }
}
```

**Note:** Different field names:
- Admin/Organizer login uses `accessToken`
- OTP verify uses `token`

## Scripts Created

1. **test-api-quick.ps1** - Quick 7-test script
   - Tests authentication & basic endpoints
   - **Result:** 7/7 passing after fixes

2. **test-critical-scenarios.ps1** - Comprehensive 20+ test suite
   - Tests full user flows (auth → create event → order → review)
   - **Result:** 5/12 passing (needs fixes)

## Next Steps

### Immediate Fixes
1. ✅ Update `DatabaseSeeder` to call `TestUsersSeeder`
2. ✅ Update `UserRole` enum with correct roles
3. ⚠️ Fix route endpoints in test scripts:
   - `/api/v1/event-categories` → `/api/v1/categories`
4. ⚠️ Investigate 403 errors:
   - Create Venue (admin token)
   - Create Coupon (organizer token)
   - List Permissions (admin token)
5. ⚠️ Investigate endpoint failures:
   - List Public Events (should work without auth)
   - List My Orders (client token)

### Further Investigation
- Verify policies for venues, coupons, permissions
- Check if List Events endpoint requires authentication
- Consider adding role management endpoints if needed

### Test Re-Run
- Re-run `test-critical-scenarios.ps1` after fixes
- Target: 100% success rate on critical scenarios

## Environment

- **Laravel Version:** 12
- **PHP Version:** 8.2.12
- **Database:** MySQL (`ticket_express_db`)
- **Server:** http://localhost:8000
- **Test Date:** 2026-06-29
- **OTP Bypass:** Enabled (code: `000000` in local env)

## Summary

✅ **Authentication system works perfectly** (4/4 tests passing)  
✅ **Token management working correctly** (both formats supported)  
✅ **Basic admin operations working** (list organizers)  
⚠️ **Route inconsistencies** (need URL corrections)  
⚠️ **Permission issues** (403 errors on some endpoints)  
⚠️ **Missing routes** (roles management not exposed in API v1)

**Overall Status:** 41.67% success rate - Good foundation, needs refinement
