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

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure notification behavior for maintenance reminders.
    |
    */
    'notifications' => [
        // Days before due date to send "upcoming" notifications
        'days_before_due' => env('MOTORPOOL_NOTIFICATION_DAYS_BEFORE', 7),

        // Cooldown in hours before creating another notification for the same vehicle/type
        'cooldown_hours' => env('MOTORPOOL_NOTIFICATION_COOLDOWN_HOURS', 24),

        // Maximum retry attempts before marking as permanently failed
        'max_retries' => env('MOTORPOOL_NOTIFICATION_MAX_RETRIES', 3),

        // Channels to use when creating notifications (email, in_app)
        'channels' => ['email', 'in_app'],

        // Which mailer to use for sending email notifications
        // Options: 'default' (uses MAIL_MAILER), 'gmail', 'yahoo', or any mailer defined in config/mail.php
        'mailer' => env('MOTORPOOL_NOTIFICATION_MAILER', 'default'),
    ],
];
