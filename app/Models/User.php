<?php

declare(strict_types=1);

namespace App\Models;

final class User
{
    /** Create a new user with a bcrypt-hashed password. Returns the new user ID. */
    public static function create(\PDO $pdo, string $username, string $password): int
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)"
        );
        $stmt->execute([':username' => $username, ':password_hash' => $hash]);
        return (int) $pdo->lastInsertId();
    }

    /** Find a user by username. Returns the row array or null. */
    public static function findByUsername(\PDO $pdo, string $username): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    /** Return all users (password_hash excluded for safety). */
    public static function all(\PDO $pdo): array
    {
        $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY created_at ASC");
        return $stmt->fetchAll() ?: [];
    }

    /** True when at least one user exists in the table. */
    public static function exists(\PDO $pdo): bool
    {
        return (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn() > 0;
    }

    /** Check whether the given plain-text password matches the stored hash. */
    public static function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, (string) $user['password_hash']);
    }

    /** Delete a user by ID. */
    public static function delete(\PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /** True when the given username is already taken. */
    public static function usernameExists(\PDO $pdo, string $username): bool
    {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        return (int) $stmt->fetchColumn() > 0;
    }
}
