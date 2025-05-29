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

    'espeak' => [
        'path' => env('ESPEAK_PATH'),
        'default_voice' => 'ru-RU',
    ],

    'python_api' => [
        'host' => env('PYTHON_API_HOST', 'python'),
        'port' => env('PYTHON_API_PORT', 8000),
    ],

    'yandex' => [
        'folder_id' => env('YANDEX_TTS_FOLDER_ID'),
        'client_id' => env('YANDEX_CLIENT_ID', 'your_oauth_client_id'),
        'iam_token' => env('YANDEX_IAM_TOKEN'),
    ],
];
