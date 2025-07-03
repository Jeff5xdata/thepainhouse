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

    'chomp' => [
        'api_key' => env('CHOMP_API_KEY'),
        'api_user' => env('CHOMP_API_USER'),
        'lookup_delay' => env('CHOMP_LOOKUP_DELAY', 500), // Delay in milliseconds
    ],

    'fatsecret' => [
        'consumer_key' => env('FATSECRET_CONSUMER_KEY'),
        'consumer_secret' => env('FATSECRET_CONSUMER_SECRET'),
        'access_token' => env('FATSECRET_ACCESS_TOKEN'),
        'token_expires_at' => env('FATSECRET_TOKEN_EXPIRES_AT'),
        'lookup_delay' => env('FATSECRET_LOOKUP_DELAY', 500), // Delay in milliseconds
    ],

];
