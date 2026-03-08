<?php

declare(strict_types=1);

namespace App\Core;

final class Database
{
    private static ?\PDO $instance = null;

    /**
     * @param array{host: string, port: int, database: string, username: string, password: string, charset?: string} $dbConfig
     */
    public static function getInstance(array $dbConfig): \PDO
    {
        if (self::$instance === null) {
            $charset = $dbConfig['charset'] ?? 'utf8mb4';
            $dsn     = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $dbConfig['host'],
                $dbConfig['port'],
                $dbConfig['database'],
                $charset
            );
            self::$instance = new \PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            self::migrate(self::$instance);
        }
        return self::$instance;
    }

    private static function migrate(\PDO $pdo): void
    {
        // Users – admin accounts that can log in to the dashboard
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            username      VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Events – OC action events with access codes
        $pdo->exec("CREATE TABLE IF NOT EXISTS events (
            id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name        VARCHAR(255) NOT NULL,
            slug        VARCHAR(255) NOT NULL UNIQUE,
            access_code VARCHAR(255) NOT NULL,
            starts_at   DATETIME     DEFAULT NULL,
            ends_at     DATETIME     DEFAULT NULL,
            is_current  TINYINT(1)   NOT NULL DEFAULT 0,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Audit log – every admin action is recorded here
        $pdo->exec("CREATE TABLE IF NOT EXISTS audit_log (
            id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id    INT          NOT NULL,
            username   VARCHAR(100) NOT NULL,
            action     VARCHAR(255) NOT NULL,
            details    TEXT         DEFAULT NULL,
            ip_address VARCHAR(45)  DEFAULT NULL,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_audit_user    (user_id),
            INDEX idx_audit_created (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }
}
