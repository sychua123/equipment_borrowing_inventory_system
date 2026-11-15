<?php
// Author: Lai Chee Kit
// Module 4 settings. Values that change per machine come from the .env file.

return [

    // ----- Module 1 (Inventory) API: equipment list + stock summary -----
    'module1' => [
        'base_url' => env('MODULE1_API_URL', 'http://127.0.0.1:8000'),
        // true = use the built-in fake data (App\Services\Api\MockModuleData)
        'mock' => env('MODULE1_USE_MOCK', true),
        'paths' => [
            'equipment' => '/api/m1/equipment',
            'stock_summary' => '/api/m1/stock-summary',
        ],
    ],

    // ----- Module 2 (Borrowing) API: borrowing records + overdue records -----
    'module2' => [
        'base_url' => env('MODULE2_API_URL', 'http://127.0.0.1:8000'),
        'mock' => env('MODULE2_USE_MOCK', true),
        'paths' => [
            'borrowing_records' => '/api/m2/borrowing-records',
            'overdue_records' => '/api/m2/overdue-records',
        ],
    ],

    // Shared key that modules send in the "X-API-KEY" header when calling each other
    'api_key' => env('MODULE_API_KEY', 'local-dev-key'),

    // Give up on another module's API after this many seconds
    'api_timeout' => 5,

    // Reuse the last successful API answer for this many minutes
    'snapshot_minutes' => 5,

    // A Resolved ticket closes by itself after this many days
    'auto_close_days' => 7,

    // A category is "low stock" when this many units or fewer are available
    'low_stock_threshold' => 2,

    // First day of the current semester (for the "This semester" report filter)
    'semester_start' => env('SEMESTER_START', '2026-11-02'),

    // Ticket photo limits
    'max_photos' => 3,
    'max_photo_kb' => 2048,
];
