<?php

declare(strict_types=1);

namespace App\Core;

final class Database
{
    private static ?\PDO $instance = null;

    public static function getInstance(string $dbPath): \PDO
    {
        if (self::$instance === null) {
            $dir = dirname($dbPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }
            self::$instance = new \PDO('sqlite:' . $dbPath, null, null, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            self::migrate(self::$instance);
        }
        return self::$instance;
    }

    private static function migrate(\PDO $pdo): void
    {
        $pdo->exec("CREATE TABLE IF NOT EXISTS events (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            name        TEXT    NOT NULL,
            slug        TEXT    NOT NULL UNIQUE,
            access_code TEXT    NOT NULL,
            starts_at   TEXT,
            ends_at     TEXT,
            is_current  INTEGER NOT NULL DEFAULT 0,
            created_at  TEXT    NOT NULL DEFAULT (datetime('now'))
        )");
    }
}
