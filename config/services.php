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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'wablas'),
    ],

    'wablas' => [
        'token' => env('WABLAS_TOKEN'),
        'secret_key' => env('WABLAS_SECRET_KEY'),
        'base_url' => env('WABLAS_BASE_URL', 'https://wablas.com'),
        'fallback_base_urls' => env(
            'WABLAS_FALLBACK_BASE_URLS',
            implode(',', [
                'https://tegal.wablas.com',
                'https://solo.wablas.com',
                'https://jogja.wablas.com',
                'https://kudus.wablas.com',
                'https://pati.wablas.com',
                'https://sby.wablas.com',
                'https://bdg.wablas.com',
                'https://deu.wablas.com',
                'https://texas.wablas.com',
            ])
        ),
    ],

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
        'base_url' => env('FONNTE_BASE_URL', 'https://api.fonnte.com'),
    ],


];
