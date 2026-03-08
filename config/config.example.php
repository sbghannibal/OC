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
        /*
         * bcrypt hash of the admin password.
         * Generate a new one with: php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
         * Default password is: change-me-in-production
         */
        'password_hash' => '$2y$10$D33TiM2v5aINduENh2oqqeIK55fyqUoUN916mk9OHTgU.2rKwn8xe',
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
