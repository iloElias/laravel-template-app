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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'vonage' => [
        'key' => env('SMS_SERVICE_KEY'),
        'secret' => env('SMS_SERVICE_SECRET'),
        'from' => env('SMS_SERVICE_FROM', env('APP_COMERCIAL_NAME')),
    ],

    'sms' => [
        'enabled' => env('SMS_SERVICE_ENABLED', false),
    ],

    'stripe' => [
        // Chave pública (usada no frontend para inicializar o Stripe.js)
        'key' => env('STRIPE_KEY'),
        // Chave privada (nunca exposta ao frontend)
        'secret' => env('STRIPE_SECRET'),
        // Segredo do webhook da conta principal (plataforma)
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        // Segredo do webhook das contas Connect (hosts)
        'connect_webhook_secret' => env('STRIPE_CONNECT_WEBHOOK_SECRET'),
        // IDs dos preços de assinatura (criados no Stripe Dashboard)
        'plan_basic_price_id' => env('STRIPE_PLAN_BASIC_PRICE_ID'),
        'plan_premium_price_id' => env('STRIPE_PLAN_PREMIUM_PRICE_ID'),
        // Taxa padrão por booking no modelo de porcentagem (%)
        'default_platform_fee_percent' => env('STRIPE_DEFAULT_PLATFORM_FEE_PERCENT', 10),
        // Taxa por booking quando o host tem assinatura ativa (pode ser 0)
        'subscription_booking_fee_percent' => env('STRIPE_SUBSCRIPTION_BOOKING_FEE_PERCENT', 0),
    ],

    'google' => [
        'places_key' => env('GOOGLE_PLACES_KEY'),
        'matrix_key' => env('GOOGLE_MATRIX_KEY'),
    ],

    'clickhouse' => [
        'protocol' => env('CLICKHOUSE_PROTOCOL', 'http'),
        'host' => env('CLICKHOUSE_HOST', '127.0.0.1'),
        'port' => env('CLICKHOUSE_PORT', 8123),
        'database' => env('CLICKHOUSE_DATABASE', 'default'),
        'username' => env('CLICKHOUSE_USERNAME', 'default'),
        'password' => env('CLICKHOUSE_PASSWORD', ''),
    ],
];
