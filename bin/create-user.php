#!/usr/bin/env php
<?php

/**
 * Create the first (or any additional) admin user.
 *
 * Usage (run from the repository root):
 *   php bin/create-user.php
 *
 * The script reads DB credentials from .env automatically.
 * If ADMIN_USERNAME and ADMIN_PASSWORD are set in .env they are used as
 * defaults; otherwise the script will prompt you interactively.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Env;
use App\Models\User;

// Load .env
Env::load(__DIR__ . '/../.env');

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    exit("ERROR: config/config.php not found. Copy config/config.example.php first.\n");
}
$config = require $configFile;

// ── Read username ──────────────────────────────────────────────────────────
$defaultUsername = (string)(getenv('ADMIN_USERNAME') ?: '');

echo "Create admin user\n";
echo "─────────────────\n";

if ($defaultUsername !== '') {
    echo "Username [{$defaultUsername}]: ";
    $username = trim((string) fgets(STDIN));
    if ($username === '') {
        $username = $defaultUsername;
    }
} else {
    echo "Username: ";
    $username = trim((string) fgets(STDIN));
}

if ($username === '') {
    exit("ERROR: Username cannot be empty.\n");
}

if (!preg_match('/^[a-zA-Z0-9_\-\.]{3,50}$/', $username)) {
    exit("ERROR: Username may only contain letters, digits, _, - and . (3–50 chars).\n");
}

// ── Read password ──────────────────────────────────────────────────────────
$defaultPassword = (string)(getenv('ADMIN_PASSWORD') ?: '');

if ($defaultPassword !== '') {
    echo "Password [use value from .env]: ";
    $password = trim((string) fgets(STDIN));
    if ($password === '') {
        $password = $defaultPassword;
    }
} else {
    echo "Password (min. 8 chars): ";
    $password = trim((string) fgets(STDIN));
}

if (strlen($password) < 8) {
    exit("ERROR: Password must be at least 8 characters.\n");
}

// ── Connect and insert ─────────────────────────────────────────────────────
try {
    $pdo = Database::getInstance($config['db']);
} catch (\PDOException $e) {
    exit("ERROR: Could not connect to the database: " . $e->getMessage() . "\n");
}

if (User::usernameExists($pdo, $username)) {
    exit("ERROR: Username \"{$username}\" already exists.\n");
}

$id = User::create($pdo, $username, $password);
echo "✓ User \"{$username}\" created (id={$id}).\n";
