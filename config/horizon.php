<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Horizon Dashboard Config
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'email' => env('HORIZON_EMAIL_NOTIFY', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Watchers
    |--------------------------------------------------------------------------
    */
    'waits' => [
        'redis:default',
        'redis:high',
        'github-webhooks',
        'ai-reviews',
    ],

    /*
    |--------------------------------------------------------------------------
    | Master Supervisor
    |--------------------------------------------------------------------------
    */
    'supervisor-1' => [
        'connection' => 'redis',
        'queue' => ['high', 'default', 'github-webhooks', 'ai-reviews'],
        'balance' => 'auto',
        'minProcesses' => 1,
        'maxProcesses' => 10,
        'memory' => 128,
        'tries' => 3,
        'nice' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Horizon Auth (only allow admins)
    |--------------------------------------------------------------------------
    */
    'notifications_traps' => [
        'email' => true,
        'slack' => env('HORIZON_SLACK_WEBHOOK'),
    ],
];
