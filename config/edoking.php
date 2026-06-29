<?php

declare(strict_types=1);

return [
    // Server URL
    'url' => env('EDOKING_SERVER_URL', ''),

    // Server Client ID
    'client_id' => env('EDOKING_CLIENT_ID', ''),

    // Server API Key
    'key' => env('EDOKING_API_KEY', ''),

    // Sender name (must be approved by Edoking)
    'sender_name' => env('EDOKING_SENDER_NAME', 'KWEEK'),
];
