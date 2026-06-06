<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Lions Academy frontends (public site + admin SPA) call this API
    | from a different origin during development and possibly production.
    | Origins are driven by the CORS_ALLOWED_ORIGINS env var (comma list).
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up', 'storage/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-Id', 'X-RateLimit-Limit', 'X-RateLimit-Remaining'],

    'max_age' => 60 * 60 * 24,

    'supports_credentials' => true,

];
