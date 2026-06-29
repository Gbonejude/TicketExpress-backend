<?php

use Laravel\Pulse\Recorders;

return [

    /*
    |--------------------------------------------------------------------------
    | Pulse Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where Pulse will be accessible from. When set
    | to null, Pulse will reside under the same domain as the application.
    | Otherwise, this value will be used as the subdomain.
    |
    */

    'domain' => env('PULSE_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Pulse Path
    |--------------------------------------------------------------------------
    |
    | This is the URI path where Pulse will be accessible from. Feel free
    | to change this path to anything you like.
    |
    */

    'path' => env('PULSE_PATH', 'pulse'),

    /*
    |--------------------------------------------------------------------------
    | Pulse Master Switch
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable all Pulse data recorders regardless
    | of their individual configurations. This provides a single option to
    | quickly disable all Pulse functionality.
    |
    */

    'enabled' => env('PULSE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Pulse Storage Driver
    |--------------------------------------------------------------------------
    |
    | This configuration option determines which storage driver will be used
    | while storing and retrieving Pulse data.
    |
    */

    'storage' => [
        'driver' => env('PULSE_STORAGE_DRIVER', 'database'),

        'database' => [
            'connection' => env('PULSE_DB_CONNECTION', null),
            'chunk' => 1000,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Ingest Driver
    |--------------------------------------------------------------------------
    |
    | This configuration option determines the ingest driver that will be used
    | to capture entries from your application.
    |
    */

    'ingest' => [
        'driver' => env('PULSE_INGEST_DRIVER', 'storage'),
        'trim' => [
            'lottery' => [1, 1_000],
            'keep' => '7 days',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Recorders
    |--------------------------------------------------------------------------
    |
    | These configuration options determine which recorders will capture data
    | for your application. You may choose to disable specific recorders to
    | speed up data ingestion or reduce database usage.
    |
    */

    'recorders' => [
        Recorders\CacheInteractions::class => [
            'enabled' => env('PULSE_CACHE_INTERACTIONS_ENABLED', true),
            'sample_rate' => env('PULSE_CACHE_INTERACTIONS_SAMPLE_RATE', 1),
            'groups' => [
                // Pattern => Label
                // '/^job\..*/' => 'Jobs',
                // '/^laravel:/' => 'Laravel',
            ],
            'ignore' => [
                '#^illuminate:#',
                '#^laravel:pulse:#',
                '#^spatie\.permission\.cache#',
            ],
        ],

        Recorders\Exceptions::class => [
            'enabled' => env('PULSE_EXCEPTIONS_ENABLED', true),
            'sample_rate' => env('PULSE_EXCEPTIONS_SAMPLE_RATE', 1),
            'capture_at' => env('PULSE_EXCEPTIONS_CAPTURE_AT', true),
            'ignore' => [
                // 'Illuminate\Http\Exceptions\HttpResponseException',
            ],
        ],

        Recorders\Queues::class => [
            'enabled' => env('PULSE_QUEUES_ENABLED', true),
            'sample_rate' => env('PULSE_QUEUES_SAMPLE_RATE', 1),
            'ignore' => [
                //
            ],
        ],

        Recorders\SlowJobs::class => [
            'enabled' => env('PULSE_SLOW_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_JOBS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_JOBS_THRESHOLD', 1000),
            'ignore' => [
                //
            ],
        ],

        Recorders\SlowOutgoingRequests::class => [
            'enabled' => env('PULSE_SLOW_OUTGOING_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_OUTGOING_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_OUTGOING_REQUESTS_THRESHOLD', 1000),
            'ignore' => [
                //
            ],
        ],

        Recorders\SlowQueries::class => [
            'enabled' => env('PULSE_SLOW_QUERIES_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_QUERIES_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 1000),
            'location' => env('PULSE_SLOW_QUERIES_LOCATION', true),
            'ignore' => [
                '#^insert into `pulse_#',
                '#^update `pulse_#',
                '#^select \* from `pulse_#',
            ],
        ],

        Recorders\SlowRequests::class => [
            'enabled' => env('PULSE_SLOW_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_SLOW_REQUESTS_SAMPLE_RATE', 1),
            'threshold' => env('PULSE_SLOW_REQUESTS_THRESHOLD', 1000),
            'ignore' => [
                '#^/pulse$#',
            ],
        ],

        Recorders\Servers::class => [
            'server_name' => env('PULSE_SERVER_NAME', gethostname()),
            'directories' => explode(':', env(
                'PULSE_SERVER_DIRECTORIES',
                '/',
            )),
        ],

        Recorders\UserJobs::class => [
            'enabled' => env('PULSE_USER_JOBS_ENABLED', true),
            'sample_rate' => env('PULSE_USER_JOBS_SAMPLE_RATE', 1),
            'ignore' => [
                //
            ],
        ],

        Recorders\UserRequests::class => [
            'enabled' => env('PULSE_USER_REQUESTS_ENABLED', true),
            'sample_rate' => env('PULSE_USER_REQUESTS_SAMPLE_RATE', 1),
            'ignore' => [
                '#^/pulse$#',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Pulse Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to every Pulse route, giving you the
    | chance to add your own middleware to this stack or override existing
    | middleware provided by Pulse.
    |
    */

    'middleware' => [
        'web',
        // Note: 'auth' middleware retiré car pas de route 'login' dans cette API
        // Pour production, créer un middleware custom qui vérifie auth()->check()
    ],
];
