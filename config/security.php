<?php

return [
    'headers' => [
        'x-frame-options' => 'SAMEORIGIN',
        'x-xss-protection' => '1; mode=block',
        'x-content-type-options' => 'nosniff',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'content-security-policy' => "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';",
        'strict-transport-security' => 'max-age=31536000; includeSubDomains',
    ],

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
        'by' => 'ip',
    ],

    'cors' => [
        'enabled' => true,
        'paths' => ['api/*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'],
        'allowed_origins' => ['*'],
        'allowed_headers' => ['*'],
        'exposed_headers' => [],
        'max_age' => 0,
        'supports_credentials' => false,
    ],

    'authentication' => [
        'jwt' => [
            'enabled' => true,
            'secret' => env('JWT_SECRET'),
            'ttl' => 60, // minutes
            'refresh_ttl' => 20160, // minutes
        ],
        'api' => [
            'enabled' => true,
            'token_expiration' => 60, // minutes
        ],
    ],

    'encryption' => [
        'key' => env('APP_KEY'),
        'cipher' => 'AES-256-CBC',
    ],

    'session' => [
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
        'encrypt' => true,
        'secure' => true,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    'sanitization' => [
        'enabled' => true,
        'strip_tags' => true,
        'encode_entities' => true,
    ],

    'logging' => [
        'enabled' => true,
        'channels' => ['daily'],
        'level' => 'error',
        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
        ],
    ],
]; 