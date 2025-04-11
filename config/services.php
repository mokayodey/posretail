<?php

return [
    // ... existing code ...

    'moniepoint' => [
        'base_url' => env('MONIEPOINT_BASE_URL', 'https://api.moniepoint.com/v1'),
        'api_key' => env('MONIEPOINT_API_KEY'),
        'secret_key' => env('MONIEPOINT_SECRET_KEY'),
    ],

    'suregifts' => [
        'base_url' => env('SUREGIFTS_BASE_URL', 'https://api.suregifts.com.ng/v1'),
        'api_key' => env('SUREGIFTS_API_KEY'),
        'secret_key' => env('SUREGIFTS_SECRET_KEY'),
    ],

    // ... existing code ...
]; 