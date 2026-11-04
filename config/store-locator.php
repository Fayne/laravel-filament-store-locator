<?php

return [
    'disk' => env('STORE_LOCATOR_DISK', 'public'),
    'routes' => [
        'enabled' => env('STORE_LOCATOR_ROUTE_ENABLED', true),
        'cached' => env('STORE_LOCATOR_ROUTE_CACHED', true),
        'max_age' => (int) env('STORE_LOCATOR_ROUTE_MAX_AGE', 3600),
    ],
];
