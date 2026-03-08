<?php

declare(strict_types=1);

namespace App\Models;

final class AuditLog
{
    /**
     * Record an admin action.
     *
     * @param int         $userId   The ID of the logged-in user
     * @param string      $username The username (denormalised for resilient querying)
     * @param string      $action   Short description, e.g. "event.create"
     * @param string|null $details  Optional human-readable context
     * @param string|null $ip       Client IP address
     */
    public static function record(
        \PDO $pdo,
        int $userId,
        string $username,
        string $action,
        ?string $details = null,
        ?string $ip = null
    ): void {
        $stmt = $pdo->prepare(
            "INSERT INTO audit_log (user_id, username, action, details, ip_address)
             VALUES (:user_id, :username, :action, :details, :ip_address)"
        );
        $stmt->execute([
            ':user_id'    => $userId,
            ':username'   => $username,
            ':action'     => $action,
            ':details'    => $details,
            ':ip_address' => $ip,
        ]);
    }

    /**
     * Return the most recent audit log entries.
     *
     * @return list<array<string,mixed>>
     */
    public static function recent(\PDO $pdo, int $limit = 200): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM audit_log ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }
}
