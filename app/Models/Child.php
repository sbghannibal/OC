<?php

declare(strict_types=1);

namespace App\Models;

final class Child
{
    /** Return all children for a given parent, ordered by first name. */
    public static function findByParent(\PDO $pdo, int $parentId): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM children WHERE parent_id = :parent_id ORDER BY first_name, last_name"
        );
        $stmt->execute([':parent_id' => $parentId]);
        return $stmt->fetchAll();
    }

    /**
     * Insert a new child record and return the new ID.
     *
     * Expected keys in $data: parent_id, first_name, last_name (nullable),
     * birthdate (nullable), klas_id (nullable), klas_name (nullable).
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO children (parent_id, first_name, last_name, birthdate, klas_id, klas_name)
             VALUES (:parent_id, :first_name, :last_name, :birthdate, :klas_id, :klas_name)"
        );
        $stmt->execute([
            ':parent_id'  => (int) $data['parent_id'],
            ':first_name' => (string) $data['first_name'],
            ':last_name'  => (isset($data['last_name']) && $data['last_name'] !== '') ? (string) $data['last_name'] : null,
            ':birthdate'  => (isset($data['birthdate']) && $data['birthdate'] !== '') ? (string) $data['birthdate'] : null,
            ':klas_id'    => (isset($data['klas_id']) && (int) $data['klas_id'] > 0) ? (int) $data['klas_id'] : null,
            ':klas_name'  => (isset($data['klas_name']) && $data['klas_name'] !== '') ? (string) $data['klas_name'] : null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * Find a child by ID belonging to a specific parent.
     * Returns null when the child does not exist or belongs to another parent.
     */
    public static function findByIdAndParent(\PDO $pdo, int $childId, int $parentId): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM children WHERE id = :id AND parent_id = :parent_id LIMIT 1"
        );
        $stmt->execute([':id' => $childId, ':parent_id' => $parentId]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Find a child that is likely a duplicate of the given name/birthdate/class
     * combination within the same parent account.
     *
     * Strategy:
     *  1. Collect all children of this parent with the same first name (case-insensitive).
     *  2. Among those, prefer one that also matches birthdate, last name, or class.
     *  3. If only one candidate exists with the same first name, return it as a
     *     probable duplicate even without secondary match.
     */
    public static function findLikelyDuplicate(
        \PDO $pdo,
        int $parentId,
        string $firstName,
        ?string $lastName,
        ?string $birthdate,
        ?int $klasId
    ): ?array {
        $stmt = $pdo->prepare(
            "SELECT * FROM children
             WHERE parent_id = :parent_id AND LOWER(first_name) = LOWER(:first_name)
             ORDER BY id DESC"
        );
        $stmt->execute([':parent_id' => $parentId, ':first_name' => $firstName]);
        $candidates = $stmt->fetchAll();

        if (empty($candidates)) {
            return null;
        }

        foreach ($candidates as $c) {
            // Strong match: birthdate provided and matches
            if ($birthdate !== null && $birthdate !== '' && (string) $c['birthdate'] === $birthdate) {
                return $c;
            }
            // Strong match: last name provided and matches
            if (
                $lastName !== null && $lastName !== '' &&
                $c['last_name'] !== null &&
                strtolower((string) $c['last_name']) === strtolower($lastName)
            ) {
                return $c;
            }
            // Moderate match: same class
            if ($klasId !== null && $klasId > 0 && (int) $c['klas_id'] === $klasId) {
                return $c;
            }
        }

        // Single candidate with same first name → likely duplicate
        if (count($candidates) === 1) {
            return $candidates[0];
        }

        return null;
    }
}
