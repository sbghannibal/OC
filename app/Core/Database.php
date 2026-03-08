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

        // Classes (klassen) – school classes that students can belong to
        $pdo->exec("CREATE TABLE IF NOT EXISTS classes (
            id         INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name       VARCHAR(20) NOT NULL UNIQUE,
            created_at DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Seed default classes if the table is empty
        self::seedClasses($pdo);

        // Registrations – participants who signed up for an event
        $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
            id              INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            event_id        INT          NOT NULL,
            naam            VARCHAR(255) NOT NULL,
            email           VARCHAR(255) NOT NULL,
            telefoon        VARCHAR(50)  DEFAULT NULL,
            klas_id         INT          DEFAULT NULL,
            klas_name       VARCHAR(20)  DEFAULT NULL,
            opmerking       TEXT         DEFAULT NULL,
            payment_status  ENUM('unknown','paid','unpaid') NOT NULL DEFAULT 'unknown',
            paid_at         DATETIME     DEFAULT NULL,
            payment_note    TEXT         DEFAULT NULL,
            created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_reg_event (event_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Add payment columns to existing registrations tables (idempotent migration)
        self::addColumnIfMissing($pdo, 'registrations', 'payment_status',
            "ALTER TABLE registrations ADD COLUMN payment_status ENUM('unknown','paid','unpaid') NOT NULL DEFAULT 'unknown'");
        self::addColumnIfMissing($pdo, 'registrations', 'paid_at',
            "ALTER TABLE registrations ADD COLUMN paid_at DATETIME DEFAULT NULL");
        self::addColumnIfMissing($pdo, 'registrations', 'payment_note',
            "ALTER TABLE registrations ADD COLUMN payment_note TEXT DEFAULT NULL");
        // Add klas columns to existing registrations (idempotent)
        self::addColumnIfMissing($pdo, 'registrations', 'klas_id',
            "ALTER TABLE registrations ADD COLUMN klas_id INT DEFAULT NULL");
        self::addColumnIfMissing($pdo, 'registrations', 'klas_name',
            "ALTER TABLE registrations ADD COLUMN klas_name VARCHAR(20) DEFAULT NULL");

        // Event option groups – configurable option groups per event (e.g. Film, Drank, Eten)
        $pdo->exec("CREATE TABLE IF NOT EXISTS event_option_groups (
            id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            event_id     INT          NOT NULL,
            name         VARCHAR(100) NOT NULL,
            max_select   INT          NOT NULL DEFAULT 1,
            is_required  TINYINT(1)   NOT NULL DEFAULT 0,
            sort_order   INT          NOT NULL DEFAULT 0,
            created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_eog_event (event_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Event option items – individual selectable items within a group
        $pdo->exec("CREATE TABLE IF NOT EXISTS event_option_items (
            id           INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            group_id     INT          NOT NULL,
            name         VARCHAR(255) NOT NULL,
            min_grade    TINYINT      NOT NULL DEFAULT 1,
            max_grade    TINYINT      NOT NULL DEFAULT 6,
            sort_order   INT          NOT NULL DEFAULT 0,
            created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_eoi_group (group_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Registration chosen option items – join table
        $pdo->exec("CREATE TABLE IF NOT EXISTS registration_option_items (
            registration_id INT NOT NULL,
            item_id         INT NOT NULL,
            PRIMARY KEY (registration_id, item_id),
            INDEX idx_roi_item (item_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Parents – public parent accounts (email + password)
        $pdo->exec("CREATE TABLE IF NOT EXISTS parents (
            id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email         VARCHAR(255) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Children – child profiles linked to a parent account
        $pdo->exec("CREATE TABLE IF NOT EXISTS children (
            id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
            parent_id  INT          NOT NULL,
            first_name VARCHAR(100) NOT NULL,
            last_name  VARCHAR(100) DEFAULT NULL,
            birthdate  DATE         DEFAULT NULL,
            klas_id    INT          DEFAULT NULL,
            klas_name  VARCHAR(20)  DEFAULT NULL,
            created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_children_parent (parent_id),
            CONSTRAINT fk_children_parent FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Add parent_id and child_id to registrations (idempotent)
        self::addColumnIfMissing($pdo, 'registrations', 'parent_id',
            "ALTER TABLE registrations ADD COLUMN parent_id INT DEFAULT NULL");
        self::addColumnIfMissing($pdo, 'registrations', 'child_id',
            "ALTER TABLE registrations ADD COLUMN child_id INT DEFAULT NULL");
    }

    /** Seed the default 12 school classes if the classes table is empty. */
    private static function seedClasses(\PDO $pdo): void
    {
        $count = (int) $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
        if ($count > 0) {
            return;
        }
        $defaults = ['1A','1B','2A','2B','3A','3B','4A','4B','5A','5B','6A','6B'];
        $stmt     = $pdo->prepare("INSERT IGNORE INTO classes (name) VALUES (:name)");
        foreach ($defaults as $name) {
            $stmt->execute([':name' => $name]);
        }
    }

    /** Add a column only when it does not yet exist (idempotent ALTER TABLE helper). */
    private static function addColumnIfMissing(\PDO $pdo, string $table, string $column, string $alterSql): void
    {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = :table AND column_name = :column"
        );
        $stmt->execute([':table' => $table, ':column' => $column]);
        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($alterSql);
        }
    }
}
