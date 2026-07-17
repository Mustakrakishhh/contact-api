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

    'brevo' => [
        'api_key' => env('BREVO_API_KEY'),
        'base_url' => env('BREVO_API_URL', 'https://api.brevo.com/v3'),
        'timeout' => (int) env('BREVO_TIMEOUT', 10),
        'connect_timeout' => (int) env('BREVO_CONNECT_TIMEOUT', 3),
        'sender' => [
            'email' => env('MAIL_FROM_ADDRESS'),
            'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Contact API')),
        ],
        'owner' => [
            'email' => env('MAIL_OWNER_EMAIL'),
            'name' => env('MAIL_OWNER_NAME', 'Владелец сайта'),
        ],
    ],

    'contact' => [
        'rate_limit' => (int) env('CONTACT_RATE_LIMIT', 5),
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

];
