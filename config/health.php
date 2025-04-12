<?php

return [
    'checks' => [
        'database' => [
            'enabled' => true,
            'connection' => 'mysql',
            'timeout' => 5,
        ],
        'redis' => [
            'enabled' => true,
            'connection' => 'default',
            'timeout' => 5,
        ],
        'cache' => [
            'enabled' => true,
            'driver' => 'redis',
            'timeout' => 5,
        ],
        'queue' => [
            'enabled' => true,
            'connection' => 'redis',
            'timeout' => 5,
        ],
        'storage' => [
            'enabled' => true,
            'disk' => 'local',
            'timeout' => 5,
        ],
        'mail' => [
            'enabled' => true,
            'timeout' => 5,
        ],
    ],

    'endpoint' => [
        'path' => 'health',
        'middleware' => ['api'],
        'response_format' => 'json',
    ],

    'notifications' => [
        'enabled' => true,
        'channels' => ['mail', 'slack'],
        'recipients' => [
            'mail' => 'admin@api.tidaretail.com',
            'slack' => 'https://hooks.slack.com/services/your-webhook',
        ],
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 60, // seconds
    ],
]; 