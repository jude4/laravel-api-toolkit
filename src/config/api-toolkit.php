<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Specify which route files should be scanned for API endpoints
    |
    */
    'route_files' => [
        'api.php', // Default Laravel API routes
        // 'api-admin.php',
        // 'api-v2.php',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | Only routes starting with this prefix will be included
    |
    */
    'prefix' => 'api',
];
