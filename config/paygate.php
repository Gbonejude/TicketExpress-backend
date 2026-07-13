<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | PayGate Global API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for PayGate Global payment gateway integration
    | Documentation: https://paygateglobal.com
    | Supported Networks: FLOOZ (Moov), TMONEY (Togocom)
    |
    */

    // SECURITY P0-10 — fallback API key was committed in plain text. Now
    // required via env (.env: PAYGATE_API_KEY). Rotate the value at PayGate
    // Global before deploying — the previous one is considered burned.
    'api_key' => env('PAYGATE_API_KEY'),

    'base_url' => env('PAYGATE_BASE_URL', 'https://paygateglobal.com'),

    'webhook_url' => env('APP_URL') . '/api/v1/webhooks/paygate',

    'currency' => env('PAYGATE_CURRENCY', 'XOF'),

    'timeout' => env('PAYGATE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Payment Methods Configuration
    |--------------------------------------------------------------------------
    | PayGate Global supports FLOOZ and TMONEY mobile money networks
    */

    'payment_methods' => [
        'mobile_money' => [
            'enabled' => true,
            'operators' => [
                // FLOOZ - Moov Money (Togo/Benin)
                'moov' => 'Moov Money (FLOOZ)',
                'flooz' => 'FLOOZ',

                // TMONEY - Togocom (Togo)
                'togocom' => 'Togocom (TMONEY)',
                'tmoney' => 'TMONEY',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */

    'endpoints' => [
        'pay' => '/api/v1/pay',
        'status' => '/api/v1/status',
        'status_by_identifier' => '/api/v2/status',
        'check_balance' => '/api/v1/check-balance',
        'payment_page' => '/v1/page',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */

    'auto_redirect' => env('PAYGATE_AUTO_REDIRECT', true),

    'return_url' => env('APP_URL') . '/payment/success',

    'cancel_url' => env('APP_URL') . '/payment/cancel',

    /*
    |--------------------------------------------------------------------------
    | Status Codes
    |--------------------------------------------------------------------------
    | PayGate transaction status codes
    */

    'status_codes' => [
        'transaction' => [
            0 => 'success',
            2 => 'invalid_token',
            4 => 'invalid_params',
            6 => 'duplicate',
        ],
        'payment' => [
            0 => 'completed',
            2 => 'processing',
            4 => 'expired',
            6 => 'cancelled',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */

    'webhook_secret' => env('PAYGATE_WEBHOOK_SECRET'),

    'verify_ssl' => env('PAYGATE_VERIFY_SSL', true),
];
