<?php

return [

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'line' => [
        'channel_secret' => env('LINE_CHANNEL_SECRET'),
        'channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),
        'base_url' => 'https://api.line.me',
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-5.4-mini'),
        'base_url' => 'https://api.openai.com/v1',
    ],


    'line_login' => [
    'channel_id' => env('LINE_CHANNEL_LOGIN_ID'),
    'channel_secret' => env('LINE_CHANNEL_LOGIN_SECRET'),
    'redirect' => env('LINE_REDIRECT_URI'),
    ],
    
];
