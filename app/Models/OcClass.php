<?php

declare(strict_types=1);

namespace App\Models;

final class OcClass
{
    /**
     * Validation pattern for class names:
     * - Lagere school: digit 1-6 followed by a single uppercase letter (e.g. 3A, 6B)
     * - Kleuter:       digit 1-3, letter K, then A or B (e.g. 1KA, 3KB)
     */
    public const NAME_PATTERN = '/^([1-3]K[AB]|[1-6][A-Z])$/';

    /**
     * Canonical rank map used both for seeding and for back-filling.
     * Kleuters sort before lagere school (ranks 10-31 vs 40-91).
     */
    public const RANK_MAP = [
        '1KA' => 10, '1KB' => 11,
        '2KA' => 20, '2KB' => 21,
        '3KA' => 30, '3KB' => 31,
        '1A'  => 40, '1B'  => 41,
        '2A'  => 50, '2B'  => 51,
        '3A'  => 60, '3B'  => 61,
        '4A'  => 70, '4B'  => 71,
        '5A'  => 80, '5B'  => 81,
        '6A'  => 90, '6B'  => 91,
    ];

    /** @return list<array<string,mixed>> */
    public static function all(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT * FROM classes ORDER BY rank ASC, name ASC");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Return the numeric rank for a class name, or 0 when the name is not in the rank map.
     */
    public static function rankForName(string $name): int
    {
        return self::RANK_MAP[$name] ?? 0;
    }

    /** @return array<string,mixed>|null */
    public static function findById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM classes WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    public static function nameExists(\PDO $pdo, string $name): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM classes WHERE name = :name");
        $stmt->execute([':name' => $name]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function create(\PDO $pdo, string $name): int
    {
        $rank = self::rankForName($name);
        $stmt = $pdo->prepare("INSERT INTO classes (name, rank) VALUES (:name, :rank)");
        $stmt->execute([':name' => $name, ':rank' => $rank]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Delete a class only if it is not referenced by any registration.
     * Returns true on success, false when the class is in use.
     */
    public static function delete(\PDO $pdo, int $id): bool
    {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM registrations WHERE klas_id = :id"
        );
        $stmt->execute([':id' => $id]);
        if ((int) $stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return true;
    }

    /**
     * Derive the numeric grade (1-6) from a class name prefix, e.g. "3A" → 3.
     * Returns null when the name does not start with a valid digit.
     */
    public static function gradeFromName(string $name): ?int
    {
        if (preg_match('/^([1-6])/', $name, $m)) {
            return (int) $m[1];
        }
        return null;
    }
}
