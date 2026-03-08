<?php

declare(strict_types=1);

namespace App\Models;

final class OcClass
{
    /** Validation pattern for class names: digit 1-6 followed by a single uppercase letter. */
    public const NAME_PATTERN = '/^[1-6][A-Z]$/';
    /** @return list<array<string,mixed>> */
    public static function all(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT * FROM classes ORDER BY name ASC");
        return $stmt->fetchAll() ?: [];
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
        $stmt = $pdo->prepare("INSERT INTO classes (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
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
