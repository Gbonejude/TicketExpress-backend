# TicketExpress Postman Collection Generator
# This script generates a complete Postman v2.1 collection for the TicketExpress API

$collection = @{
    info = @{
        _postman_id = "ticketexpress-api-v1"
        name = "TicketExpress API v1"
        description = "Complete API collection for TicketExpress - Event Ticketing Platform"
        schema = "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    }
    auth = @{
        type = "bearer"
        bearer = @(
            @{
                key = "token"
                value = "{{admin_token}}"
                type = "string"
            }
        )
    }
    variable = @(
        @{ key = "base_url"; value = "http://localhost:8000/api/v1"; type = "string" }
        @{ key = "admin_token"; value = ""; type = "string" }
        @{ key = "organizer_token"; value = ""; type = "string" }
        @{ key = "user_token"; value = ""; type = "string" }
        @{ key = "event_id"; value = ""; type = "string" }
        @{ key = "ticket_type_id"; value = ""; type = "string" }
        @{ key = "order_id"; value = ""; type = "string" }
        @{ key = "coupon_id"; value = ""; type = "string" }
        @{ key = "category_id"; value = ""; type = "string" }
        @{ key = "venue_id"; value = ""; type = "string" }
    )
    item = @()
}

# Helper function to create test scripts
function Get-TestScript {
    param(
        [int]$expectedStatus = 200,
        [string[]]$additionalTests = @()
    )
    
    $tests = @(
        "pm.test(`"Status code is $expectedStatus`", function () {"
        "    pm.response.to.have.status($expectedStatus);"
        "});"
        ""
        "pm.test(`"Response has success structure`", function () {"
        "    const jsonData = pm.response.json();"
        "    pm.expect(jsonData).to.have.property('success');"
        "    pm.expect(jsonData).to.have.property('message');"
        "});"
    )
    
    $tests += $additionalTests
    return $tests
}
