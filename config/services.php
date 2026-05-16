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

    'google_places' => [
        'api_key' => env('GOOGLE_PLACES_API_KEY'),
    ],

    'adsense' => [
        'client' => env('GOOGLE_ADSENSE_CLIENT', 'ca-pub-5987335767779300'),
    ],

    'audiotel' => [
        'enabled' => env('AUDIOTEL_ENABLED', false),
        'id_client' => env('AUDIOTEL_ID_CLIENT', 'BWIAPVJkBWEDYQBuB3MENlZiUzJWMAc7'),
        'id_service' => env('AUDIOTEL_ID_SERVICE', 'BWIAPVJkBWQDZgA1B28ELFY5UzdWNQcxAj0='),
    ],

];
