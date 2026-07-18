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

    // REST Countries API (gratis, tidak perlu API key)
    'rest_countries' => [
        'base_url' => 'https://restcountries.com/v3.1',
    ],

    // OpenWeather API
    'openweather' => [
        'api_key' => env('OPENWEATHER_API_KEY'),
        'base_url' => 'https://api.openweathermap.org/data/2.5',
        'units' => env('OPENWEATHER_UNITS', 'metric'),
        'default_city' => env('OPENWEATHER_DEFAULT_CITY', 'Jakarta'),
    ],

    // ExchangeRate API (gratis, tidak perlu API key)
    'exchange_rate' => [
        'base_url' => 'https://open.er-api.com/v6',
    ],

    // World Bank API (gratis, tidak perlu API key)
    'world_bank' => [
        'base_url' => 'http://api.worldbank.org/v2',
    ],

    // NewsAPI
    'newsapi' => [
        'api_key' => env('NEWS_API_KEY'),
        'base_url' => 'https://newsapi.org/v2',
        'page_size' => env('NEWSAPI_PAGE_SIZE', 50),
        'language' => env('NEWSAPI_LANGUAGE', 'en'),
    ],

    // OpenStreetMap / Leaflet (gratis, tidak perlu API key)
    'openstreetmap' => [
        'tile_url' => 'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
        'attribution' => '&copy; OpenStreetMap &copy; CARTO',
    ],

    // Nominatim API (gratis, tidak perlu API key)
    'nominatim' => [
        'base_url' => 'https://nominatim.openstreetmap.org',
    ],

];
