<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the health check system.
    |
    */

    'checkers' => [
        'database' => [
            'enabled' => true,
            'connection' => env('DB_CONNECTION', 'mysql'),
        ],
        'cache' => [
            'enabled' => true,
            'driver' => env('CACHE_DRIVER', 'file'),
        ],
        'redis' => [
            'enabled' => true,
            'connection' => env('REDIS_CONNECTION', 'default'),
        ],
        'mail' => [
            'enabled' => true,
            'driver' => env('MAIL_MAILER', 'smtp'),
        ],
    ],

    'notifications' => [
        'enabled' => true,
        'channels' => ['mail', 'slack'],
        'mail' => [
            'to' => env('HEALTH_NOTIFICATION_EMAIL', 'admin@posretail.pipeops.app'),
        ],
        'slack' => [
            'webhook_url' => env('HEALTH_SLACK_WEBHOOK_URL'),
        ],
    ],

    'endpoints' => [
        'health' => '/health',
        'metrics' => '/metrics',
    ],

    'domain' => env('APP_URL', 'https://posretail.pipeops.app'),
]; 