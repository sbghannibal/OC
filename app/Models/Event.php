<?php

declare(strict_types=1);

namespace App\Models;

final class Event
{
    /**
     * Create a new event. The newly created event automatically becomes the current event.
     *
     * @param array{name: string, slug: string, access_code: string, starts_at?: string, ends_at?: string} $data
     */
    public static function create(\PDO $pdo, array $data): int
    {
        // Clear current flag from all events first
        $pdo->exec("UPDATE events SET is_current = 0");

        $stmt = $pdo->prepare(
            "INSERT INTO events (name, slug, access_code, starts_at, ends_at, is_current)
             VALUES (:name, :slug, :access_code, :starts_at, :ends_at, 1)"
        );
        $stmt->execute([
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':access_code' => $data['access_code'],
            ':starts_at'   => $data['starts_at'] ?? null,
            ':ends_at'     => $data['ends_at'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public static function findCurrent(\PDO $pdo): ?array
    {
        $stmt = $pdo->query(
            "SELECT * FROM events WHERE is_current = 1 ORDER BY created_at DESC LIMIT 1"
        );
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    /** @return list<array<string,mixed>> */
    public static function all(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT * FROM events ORDER BY created_at DESC");
        return $stmt->fetchAll() ?: [];
    }

    public static function setCurrent(\PDO $pdo, int $id): void
    {
        $pdo->exec("UPDATE events SET is_current = 0");
        $stmt = $pdo->prepare("UPDATE events SET is_current = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public static function slugExists(\PDO $pdo, string $slug): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE slug = :slug");
        $stmt->execute([':slug' => $slug]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
