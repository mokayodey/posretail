<?php

return [
    'headers' => [
        'x-frame-options' => 'SAMEORIGIN',
        'x-xss-protection' => '1; mode=block',
        'x-content-type-options' => 'nosniff',
        'referrer-policy' => 'strict-origin-when-cross-origin',
        'content-security-policy' => "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; img-src 'self' https: data:; font-src 'self' https: data:;",
        'strict-transport-security' => 'max-age=31536000; includeSubDomains; preload',
        'permissions-policy' => 'geolocation=(), microphone=(), camera=()',
    ],

    'rate_limiting' => [
        'enabled' => true,
        'max_attempts' => 60,
        'decay_minutes' => 1,
        'by' => 'ip',
        'throttle' => [
            'enabled' => true,
            'max_attempts' => 5,
            'decay_minutes' => 1,
        ],
    ],

    'cors' => [
        'enabled' => true,
        'paths' => ['api/*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        'allowed_origins' => ['https://posretail-api.pipeops.app'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With'],
        'exposed_headers' => ['Authorization'],
        'max_age' => 86400,
        'supports_credentials' => true,
    ],

    'authentication' => [
        'jwt' => [
            'enabled' => true,
            'secret' => env('JWT_SECRET'),
            'ttl' => 60, // minutes
            'refresh_ttl' => 20160, // minutes
            'algo' => 'HS256',
            'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
        ],
        'api' => [
            'enabled' => true,
            'token_expiration' => 60, // minutes
            'rate_limit' => 60, // requests per minute
        ],
    ],

    'encryption' => [
        'key' => env('APP_KEY'),
        'cipher' => 'AES-256-CBC',
        'hash' => 'sha256',
    ],

    'session' => [
        'lifetime' => 120, // minutes
        'expire_on_close' => false,
        'encrypt' => true,
        'secure' => true,
        'http_only' => true,
        'same_site' => 'lax',
        'cookie' => 'posretail_session',
    ],

    'sanitization' => [
        'enabled' => true,
        'strip_tags' => true,
        'encode_entities' => true,
        'remove_invisible_characters' => true,
    ],

    'logging' => [
        'enabled' => true,
        'channels' => ['daily', 'slack'],
        'level' => 'error',
        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            'secret',
            'credit_card',
            'cvv',
        ],
    ],

    'maintenance' => [
        'enabled' => false,
        'allowed_ips' => [],
        'retry_after' => 60,
    ],
]; 