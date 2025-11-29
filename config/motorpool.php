<?php

declare(strict_types=1);

return [
    // Layout mode: 'topnav' (Breeze-style top navigation) or 'sidebar' (left sidebar layout)
    'layout' => env('MOTORPOOL_LAYOUT', 'topnav'),

    /*
    |--------------------------------------------------------------------------
    | Maintenance Settings
    |--------------------------------------------------------------------------
    |
    | Configure default intervals for automatic next-maintenance calculation.
    |
    */
    'maintenance' => [
        // Default interval in months for time-based maintenance scheduling
        'default_months_interval' => env('MOTORPOOL_MAINTENANCE_MONTHS_INTERVAL', 6),

        // Default interval in kilometers for odometer-based maintenance scheduling
        'default_kilometers_interval' => env('MOTORPOOL_MAINTENANCE_KM_INTERVAL', 5000),
    ],
];
