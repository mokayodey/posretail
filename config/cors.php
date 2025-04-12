<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
    'allowed_origins' => [
        'https://posretail.pipeops.app',
        'https://api.tidaretail.com', // During transition period
        'http://localhost:3000', // For local development
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => ['Authorization'],
    'max_age' => 86400, // 24 hours
    'supports_credentials' => true,
]; 