<?php

declare(strict_types=1);

return [
    /*
     * Base URL path, e.g. '/OC/public'. Leave empty for root installs.
     * Auto-detected from SCRIPT_NAME when set to ''.
     */
    'base_path' => '',

    /* Admin dashboard credentials */
    'admin' => [
        'password' => 'change-me-in-production',
    ],

    /* SQLite database file path (writable location) */
    'db_path' => __DIR__ . '/../data/oc.db',

    'sepa' => [
        // Vul in met echte gegevens voor productie-gebruik.
        'beneficiary_name' => '',
        'iban'             => '',
        'bic'              => '',
    ],
];
