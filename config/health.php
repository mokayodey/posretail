<?php

return [
    'checkers' => [
        'database' => [
            'enabled' => true,
            'connection' => 'mysql',
            'timeout' => 5,
        ],
        'cache' => [
            'enabled' => true,
            'store' => 'redis',
            'timeout' => 5,
        ],
        'redis' => [
            'enabled' => true,
            'connection' => 'default',
            'timeout' => 5,
        ],
        'mail' => [
            'enabled' => true,
            'mailer' => 'smtp',
            'timeout' => 5,
        ],
    ],

    'notifications' => [
        'enabled' => true,
        'channels' => ['mail', 'slack'],
        'mail' => [
            'to' => 'admin@posretail.pipeops.app',
            'from' => 'health@posretail.pipeops.app',
        ],
        'slack' => [
            'webhook_url' => env('SLACK_WEBHOOK_URL'),
            'channel' => '#health',
        ],
    ],

    'cache' => [
        'enabled' => true,
        'store' => 'redis',
        'key' => 'health:status',
        'ttl' => 300, // 5 minutes
    ],

    'endpoints' => [
        'health' => '/api/v1/health',
        'status' => '/api/v1/status',
    ],
]; 