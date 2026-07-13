<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | SECURITY P1-12 — Previous configuration had `allowed_origins: ['*']`
    | combined with `supports_credentials: true`. Browsers reject that
    | combination outright for credentialed requests, so the practical
    | effect was either a confusing partial failure or a permissive
    | downgrade depending on the middleware in front.
    |
    | The list below explicitly enumerates the origins our own clients
    | call from. Mobile clients (Flutter on iOS/Android) do NOT enforce
    | CORS — they live outside the browser sandbox — so this config
    | only matters for the web dashboards.
    */

    'paths' => ['api/*', 'webhooks/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Explicit allowlist via env. Default empty in dev to force the
    // operator to think about it. Production sets:
    //   CORS_ALLOWED_ORIGINS=https://resto.kweek.co,https://togo.kweek.co,...
    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')),
    ))),

    // Wildcard subdomains of kweek.co are covered by this pattern so
    // we don't have to maintain one line per country env (togo, ghana,
    // guinea, gambia, rdcongo, develop, preprod, …).
    'allowed_origins_patterns' => [
        '#^https://([a-z0-9-]+\.)?kweek\.co$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 60 * 60,

    'supports_credentials' => true,

];
