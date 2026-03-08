<?php

declare(strict_types=1);

namespace App\Models;

final class ParentUser
{
    /** Insert a new parent account and return the new ID. */
    public static function create(\PDO $pdo, string $email, string $password): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO parents (email, password_hash) VALUES (:email, :hash)"
        );
        $stmt->execute([':email' => $email, ':hash' => $hash]);
        return (int) $pdo->lastInsertId();
    }

    /** Find a parent by e-mail address, or return null if not found. */
    public static function findByEmail(\PDO $pdo, string $email): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM parents WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Verify a plain-text password against the stored hash. */
    public static function verifyPassword(array $row, string $password): bool
    {
        return password_verify($password, (string) $row['password_hash']);
    }

    /** Check whether an e-mail address is already registered. */
    public static function emailExists(\PDO $pdo, string $email): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM parents WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
