<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sync Tracker Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for configuring the sync tracker package.
    |
    */

    // The table name used to store sync tracking information
    'table_name' => 'sync_tracked_entities',

    // Default tracking options
    'default_tracking' => [
        // Whether to track creation timestamps by default
        'track_created' => true,

        // Whether to track update timestamps by default
        'track_updated' => true,

        // Whether to track deletion timestamps by default
        'track_deleted' => true,
    ],

    // Custom tracking models configuration
    'models' => [
        // Example:
        // App\Models\User::class => [
        //     'track_created' => true,
        //     'track_updated' => false,
        //     'track_deleted' => true,
        // ],
    ],
];
