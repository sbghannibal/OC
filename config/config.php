<?php

declare(strict_types=1);

// Helper: read an env var, falling back to $default when not set.
$env = static fn(string $key, string $default = ''): string
    => (string)(getenv($key) !== false ? getenv($key) : $default);

return [
    /*
     * Base URL path, e.g. '/OC/public'. Leave empty for root installs.
     * Set APP_BASE_PATH in your .env file, or leave it unset for auto-detection.
     */
    'base_path' => $env('APP_BASE_PATH'),

    /* MySQL database connection */
    'db' => [
        'host'     => $env('DB_HOST', 'localhost'),
        'port'     => (int) $env('DB_PORT', '3306'),
        'database' => $env('DB_DATABASE'),
        'username' => $env('DB_USERNAME'),
        'password' => $env('DB_PASSWORD'),
        'charset'  => 'utf8mb4',
    ],

    'sepa' => [
        'beneficiary_name' => $env('SEPA_NAME'),
        'iban'             => $env('SEPA_IBAN'),
        'bic'              => $env('SEPA_BIC'),
    ],

    /*
     * HMAC signing key for QR-link tokens (GET /events/{slug}/qr).
     * Set APP_SIGNING_KEY in .env to a long, random secret string.
     */
    'signing_key' => $env('APP_SIGNING_KEY'),
];
