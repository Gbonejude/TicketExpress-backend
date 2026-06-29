# TicketExpress API Integration Test Script
# PowerShell script to test all critical API flows

param(
    [string]$BaseUrl = "http://localhost:8000/api/v1",
    [string]$AdminEmail = "admin@ticketexpress.tg",
    [string]$AdminPassword = "password"
)

# Color output functions
function Write-Success { param([string]$message) Write-Host "✓ $message" -ForegroundColor Green }
function Write-Error { param([string]$message) Write-Host "✗ $message" -ForegroundColor Red }
function Write-Info { param([string]$message) Write-Host "ℹ $message" -ForegroundColor Cyan }
function Write-Test { param([string]$message) Write-Host "→ $message" -ForegroundColor Yellow }

# Global variables
$script:TestResults = @()
$script:AdminToken = $null
$script:CategoryId = $null
$script:VenueId = $null
$script:EventId = $null
$script:TicketTypeId = $null
$script:CouponId = $null
$script:OrderId = $null

# Test result tracking
function Add-TestResult {
    param([string]$Test, [bool]$Passed, [string]$Message = "")
    $script:TestResults += [PSCustomObject]@{
        Test = $Test
        Status = if ($Passed) { "PASS" } else { "FAIL" }
        Message = $Message
        Timestamp = Get-Date -Format "HH:mm:ss"
    }
}

# API helper function
function Invoke-Api {
    param(
        [string]$Method,
        [string]$Endpoint,
        [object]$Body = $null,
        [string]$Token = $null,
        [bool]$NoAuth = $false
    )
    
    $headers = @{
        "Accept" = "application/json"
        "Content-Type" = "application/json"
    }
    
    if (-not $NoAuth -and $Token) {
        $headers["Authorization"] = "Bearer $Token"
    }
    
    $url = "$BaseUrl$Endpoint"
    
    try {
        $params = @{
            Uri = $url
            Method = $Method
            Headers = $headers
            ErrorAction = "Stop"
        }
        
        if ($Body) {
            $params.Body = ($Body | ConvertTo-Json -Depth 10)
        }
        
        $response = Invoke-RestMethod @params
        return @{ Success = $true; Data = $response; StatusCode = 200 }
    }
    catch {
        $statusCode = if ($_.Exception.Response) { $_.Exception.Response.StatusCode.value__ } else { 0 }
        $errorBody = $null
        
        if ($_.Exception.Response) {
            $reader = New-Object System.IO.StreamReader($_.Exception.Response.GetResponseStream())
            $errorBody = $reader.ReadToEnd() | ConvertFrom-Json
            $reader.Close()
        }
        
        return @{ 
            Success = $false
            Error = $_.Exception.Message
            StatusCode = $statusCode
            Data = $errorBody
        }
    }
}

Write-Host "`n╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║     TicketExpress API Integration Tests                      ║" -ForegroundColor Magenta
Write-Host "╚══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Magenta

Write-Info "Base URL: $BaseUrl"
Write-Info "Start Time: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')"
Write-Host ""

# TEST 1: Authentication Flow
Write-Host "`n═══ Test 1: Authentication Flow ═══`n" -ForegroundColor Cyan

Write-Test "Admin Login"
$loginResult = Invoke-Api -Method POST -Endpoint "/auth/admin/login" -NoAuth $true -Body @{
    email = $AdminEmail
    password = $AdminPassword
}

if ($loginResult.Success -and $loginResult.Data.data.token) {
    $script:AdminToken = $loginResult.Data.data.token
    Write-Success "Admin login successful - Token received"
    Add-TestResult -Test "Admin Login" -Passed $true
}
else {
    Write-Error "Admin login failed: $($loginResult.Error)"
    Add-TestResult -Test "Admin Login" -Passed $false -Message $loginResult.Error
    Write-Error "Cannot continue without authentication. Exiting..."
    exit 1
}

# TEST 2: Event Creation Flow
Write-Host "`n═══ Test 2: Event Creation Flow ═══`n" -ForegroundColor Cyan

Write-Test "Create Event Category"
$categoryResult = Invoke-Api -Method POST -Endpoint "/event-categories" -Token $script:AdminToken -Body @{
    name = "Music Festival"
    description = "Musical events and concerts"
}

if ($categoryResult.Success) {
    $script:CategoryId = $categoryResult.Data.data.id
    Write-Success "Category created: $($script:CategoryId)"
    Add-TestResult -Test "Create Category" -Passed $true
}
else {
    Write-Error "Failed to create category"
    Add-TestResult -Test "Create Category" -Passed $false
}

Write-Test "Create Venue"
$venueResult = Invoke-Api -Method POST -Endpoint "/venues" -Token $script:AdminToken -Body @{
    name = "Test Stadium"
    address = "123 Test Street"
    city = "Lomé"
    capacity = 5000
}

if ($venueResult.Success) {
    $script:VenueId = $venueResult.Data.data.id
    Write-Success "Venue created: $($script:VenueId)"
    Add-TestResult -Test "Create Venue" -Passed $true
}
else {
    Write-Error "Failed to create venue"
    Add-TestResult -Test "Create Venue" -Passed $false
}

Write-Test "Create Event with Ticket Types"
$eventResult = Invoke-Api -Method POST -Endpoint "/events" -Token $script:AdminToken -Body @{
    title = "Test Summer Festival"
    description = "Integration test event"
    event_category_id = $script:CategoryId
    venue_id = $script:VenueId
    start_date = "2024-08-01"
    start_time = "18:00:00"
    end_date = "2024-08-01"
    end_time = "23:00:00"
    ticket_types = @(
        @{
            name = "VIP"
            description = "VIP access"
            price = 15000
            quantity = 10
            max_per_order = 5
        },
        @{
            name = "Standard"
            description = "General admission"
            price = 5000
            quantity = 50
            max_per_order = 10
        }
    )
}

if ($eventResult.Success) {
    $script:EventId = $eventResult.Data.data.id
    if ($eventResult.Data.data.ticketTypes -and $eventResult.Data.data.ticketTypes.Count -gt 0) {
        $script:TicketTypeId = $eventResult.Data.data.ticketTypes[0].id
        Write-Success "Event created with $($eventResult.Data.data.ticketTypes.Count) ticket types"
        Write-Info "Event ID: $($script:EventId)"
        Write-Info "Ticket Type ID: $($script:TicketTypeId)"
        Add-TestResult -Test "Create Event with Ticket Types" -Passed $true
    }
    else {
        Write-Error "Event created but no ticket types found"
        Add-TestResult -Test "Create Event with Ticket Types" -Passed $false
    }
}
else {
    Write-Error "Failed to create event: $($eventResult.Error)"
    Add-TestResult -Test "Create Event with Ticket Types" -Passed $false
}

Write-Test "Publish Event"
$publishResult = Invoke-Api -Method POST -Endpoint "/events/$($script:EventId)/publish" -Token $script:AdminToken

if ($publishResult.Success) {
    Write-Success "Event published successfully"
    Add-TestResult -Test "Publish Event" -Passed $true
}
else {
    Write-Error "Failed to publish event"
    Add-TestResult -Test "Publish Event" -Passed $false
}

# TEST 3: Guest Checkout Flow (CRITICAL)
Write-Host "`n═══ Test 3: Guest Checkout Flow (CRITICAL) ═══`n" -ForegroundColor Cyan

Write-Test "Create Coupon"
$couponResult = Invoke-Api -Method POST -Endpoint "/coupons" -Token $script:AdminToken -Body @{
    code = "TEST20"
    discount_percent = 20
    max_uses = 100
    valid_from = (Get-Date).ToString("yyyy-MM-dd")
    valid_until = (Get-Date).AddMonths(3).ToString("yyyy-MM-dd")
}

if ($couponResult.Success) {
    $script:CouponId = $couponResult.Data.data.id
    Write-Success "Coupon created: TEST20 (20% off)"
    Add-TestResult -Test "Create Coupon" -Passed $true
}
else {
    Write-Error "Failed to create coupon"
    Add-TestResult -Test "Create Coupon" -Passed $false
}

Write-Test "Validate Coupon (Public Endpoint)"
$validateResult = Invoke-Api -Method GET -Endpoint "/coupons/validate?code=TEST20" -NoAuth $true

if ($validateResult.Success) {
    Write-Success "Coupon validated successfully"
    Add-TestResult -Test "Validate Coupon" -Passed $true
}
else {
    Write-Error "Failed to validate coupon"
    Add-TestResult -Test "Validate Coupon" -Passed $false
}

Write-Test "Create Order WITHOUT Authentication (Guest Checkout)"
$orderResult = Invoke-Api -Method POST -Endpoint "/orders" -NoAuth $true -Body @{
    event_id = $script:EventId
    customer_name = "Test Guest"
    customer_email = "guest@test.com"
    customer_phone = "+22890123456"
    tickets = @(
        @{
            ticket_type_id = $script:TicketTypeId
            quantity = 3
        }
    )
}

if ($orderResult.Success) {
    $script:OrderId = $orderResult.Data.data.id
    Write-Success "Guest order created successfully"
    Write-Info "Order ID: $($script:OrderId)"
    Write-Info "Total Amount: $($orderResult.Data.data.total_amount)"
    Add-TestResult -Test "Guest Checkout" -Passed $true
}
else {
    Write-Error "Failed to create guest order: $($orderResult.Error)"
    Add-TestResult -Test "Guest Checkout" -Passed $false
}

Write-Test "Verify Stock Decremented"
$eventCheckResult = Invoke-Api -Method GET -Endpoint "/events/$($script:EventId)" -Token $script:AdminToken

if ($eventCheckResult.Success) {
    $ticketType = $eventCheckResult.Data.data.ticketTypes | Where-Object { $_.id -eq $script:TicketTypeId }
    if ($ticketType) {
        $expectedSold = 3
        $actualSold = $ticketType.soldQuantity
        
        if ($actualSold -eq $expectedSold) {
            Write-Success "Stock correctly decremented: $actualSold tickets sold"
            Add-TestResult -Test "Stock Decrement" -Passed $true
        }
        else {
            Write-Error "Stock mismatch: Expected $expectedSold, Got $actualSold"
            Add-TestResult -Test "Stock Decrement" -Passed $false -Message "Expected $expectedSold, Got $actualSold"
        }
    }
}

Write-Test "Cancel Order"
$cancelResult = Invoke-Api -Method POST -Endpoint "/orders/$($script:OrderId)/cancel" -Token $script:AdminToken

if ($cancelResult.Success) {
    Write-Success "Order cancelled successfully"
    Add-TestResult -Test "Cancel Order" -Passed $true
}
else {
    Write-Error "Failed to cancel order"
    Add-TestResult -Test "Cancel Order" -Passed $false
}

Write-Test "Verify Stock Released After Cancellation"
$eventCheck2Result = Invoke-Api -Method GET -Endpoint "/events/$($script:EventId)" -Token $script:AdminToken

if ($eventCheck2Result.Success) {
    $ticketType = $eventCheck2Result.Data.data.ticketTypes | Where-Object { $_.id -eq $script:TicketTypeId }
    if ($ticketType) {
        $expectedSold = 0
        $actualSold = $ticketType.soldQuantity
        
        if ($actualSold -eq $expectedSold) {
            Write-Success "Stock correctly released: $actualSold tickets sold (stock restored)"
            Add-TestResult -Test "Stock Release" -Passed $true
        }
        else {
            Write-Error "Stock not released: Expected $expectedSold, Got $actualSold"
            Add-TestResult -Test "Stock Release" -Passed $false
        }
    }
}

# TEST 4: Stock Management Test
Write-Host "`n═══ Test 4: Stock Management Test ═══`n" -ForegroundColor Cyan

Write-Test "Attempt to Purchase More Than Available"
$oversellResult = Invoke-Api -Method POST -Endpoint "/orders" -NoAuth $true -Body @{
    event_id = $script:EventId
    customer_name = "Oversell Test"
    customer_email = "oversell@test.com"
    customer_phone = "+22890999999"
    tickets = @(
        @{
            ticket_type_id = $script:TicketTypeId
            quantity = 100
        }
    )
}

if (-not $oversellResult.Success -and $oversellResult.StatusCode -eq 422) {
    Write-Success "Overselling prevented correctly (422 error)"
    Write-Info "Error message: $($oversellResult.Data.message)"
    Add-TestResult -Test "Prevent Overselling" -Passed $true
}
else {
    Write-Error "Overselling was NOT prevented - this is a critical bug!"
    Add-TestResult -Test "Prevent Overselling" -Passed $false -Message "System allowed overselling"
}

# TEST 5: Coupon Application Test
Write-Host "`n═══ Test 5: Coupon Application Test ═══`n" -ForegroundColor Cyan

Write-Test "Create Order with Coupon Code"
$couponOrderResult = Invoke-Api -Method POST -Endpoint "/orders" -NoAuth $true -Body @{
    event_id = $script:EventId
    customer_name = "Coupon Test User"
    customer_email = "coupon@test.com"
    customer_phone = "+22890777777"
    coupon_code = "TEST20"
    tickets = @(
        @{
            ticket_type_id = $script:TicketTypeId
            quantity = 2
        }
    )
}

if ($couponOrderResult.Success) {
    $subtotal = 2 * 5000  # 2 tickets * 5000 price
    $expectedDiscount = $subtotal * 0.20
    $expectedTotal = $subtotal - $expectedDiscount
    
    $actualTotal = $couponOrderResult.Data.data.total_amount
    $actualDiscount = if ($couponOrderResult.Data.data.discount_amount) { $couponOrderResult.Data.data.discount_amount } else { 0 }
    
    if ($actualDiscount -eq $expectedDiscount -and $actualTotal -eq $expectedTotal) {
        Write-Success "Coupon applied correctly"
        Write-Info "Subtotal: $subtotal, Discount: $actualDiscount, Total: $actualTotal"
        Add-TestResult -Test "Coupon Application" -Passed $true
    }
    else {
        Write-Error "Coupon calculation incorrect"
        Write-Info "Expected: Discount=$expectedDiscount, Total=$expectedTotal"
        Write-Info "Actual: Discount=$actualDiscount, Total=$actualTotal"
        Add-TestResult -Test "Coupon Application" -Passed $false
    }
}
else {
    Write-Error "Failed to create order with coupon"
    Add-TestResult -Test "Coupon Application" -Passed $false
}

# Generate Test Report
Write-Host "`n╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Magenta
Write-Host "║                    TEST RESULTS SUMMARY                      ║" -ForegroundColor Magenta
Write-Host "╚══════════════════════════════════════════════════════════════╝`n" -ForegroundColor Magenta

$passCount = ($script:TestResults | Where-Object { $_.Status -eq "PASS" }).Count
$failCount = ($script:TestResults | Where-Object { $_.Status -eq "FAIL" }).Count
$totalCount = $script:TestResults.Count

Write-Host "Total Tests: $totalCount" -ForegroundColor White
Write-Host "Passed: $passCount" -ForegroundColor Green
Write-Host "Failed: $failCount" -ForegroundColor $(if ($failCount -eq 0) { "Green" } else { "Red" })
Write-Host ""

# Detailed results
$script:TestResults | ForEach-Object {
    $color = if ($_.Status -eq "PASS") { "Green" } else { "Red" }
    $symbol = if ($_.Status -eq "PASS") { "✓" } else { "✗" }
    Write-Host "$symbol [$($_.Timestamp)] $($_.Test): $($_.Status)" -ForegroundColor $color
    if ($_.Message) {
        Write-Host "  → $($_.Message)" -ForegroundColor Yellow
    }
}

# Save results to file
$reportPath = Join-Path (Get-Location) "test-results.txt"
$report = @"
TicketExpress API Integration Test Results
==========================================
Date: $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")
Base URL: $BaseUrl

Summary:
--------
Total Tests: $totalCount
Passed: $passCount
Failed: $failCount

Detailed Results:
-----------------
$($script:TestResults | ForEach-Object { "[$($_.Timestamp)] $($_.Test): $($_.Status) $($_.Message)" } | Out-String)

Configuration:
--------------
Admin Email: $AdminEmail
Category ID: $script:CategoryId
Venue ID: $script:VenueId
Event ID: $script:EventId
Ticket Type ID: $script:TicketTypeId
Coupon ID: $script:CouponId
Order ID: $script:OrderId
"@

$report | Out-File -FilePath $reportPath -Encoding UTF8
Write-Host "`nTest results saved to: $reportPath" -ForegroundColor Cyan

# Exit with error code if any tests failed
if ($failCount -gt 0) {
    Write-Host "`n⚠ Some tests failed. Please review the results above.`n" -ForegroundColor Red
    exit 1
}
else {
    Write-Host "`n✓ All tests passed successfully!`n" -ForegroundColor Green
    exit 0
}
