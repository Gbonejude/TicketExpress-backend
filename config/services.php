<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'paygate' => [
        'api_key' => env('PAYGATE_API_KEY'),
        'base_url' => env('PAYGATE_BASE_URL', 'https://paygateglobal.com'),
        'currency' => env('PAYGATE_CURRENCY', 'XOF'),
        'timeout' => (int) env('PAYGATE_TIMEOUT', 30),
        'webhook_secret' => env('PAYGATE_WEBHOOK_SECRET'),
        'verify_ssl' => filter_var(env('PAYGATE_VERIFY_SSL', true), FILTER_VALIDATE_BOOL),
        'auto_redirect' => filter_var(env('PAYGATE_AUTO_REDIRECT', true), FILTER_VALIDATE_BOOL),

        /*
         * Refuser d'approuver un retrait que le solde marchand ne couvre pas.
         *
         * Désactivé par défaut, et volontairement : `check-balance` exige une IP
         * whitelistée et une clé valide, sinon il répond un solde nul —
         * indistinguable d'un compte réellement vide, ce qui bloquerait toute
         * approbation en développement. À activer en production, une fois l'IP
         * du serveur déclarée chez PayGate.
         */
        'enforce_payout_balance' => filter_var(
            env('PAYGATE_ENFORCE_PAYOUT_BALANCE', false),
            FILTER_VALIDATE_BOOL,
        ),
    ],

];
