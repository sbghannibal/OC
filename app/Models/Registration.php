<?php

declare(strict_types=1);

namespace App\Models;

final class Registration
{
    /**
     * Persist a new registration for an event.
     *
     * @param array{event_id: int, naam: string, email: string, telefoon?: string, opmerking?: string} $data
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO registrations (event_id, naam, email, telefoon, opmerking)
             VALUES (:event_id, :naam, :email, :telefoon, :opmerking)"
        );
        $stmt->execute([
            ':event_id' => $data['event_id'],
            ':naam'     => $data['naam'],
            ':email'    => $data['email'],
            ':telefoon' => $data['telefoon'] ?? null,
            ':opmerking'=> $data['opmerking'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public static function findByEvent(\PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM registrations WHERE event_id = :event_id ORDER BY created_at ASC"
        );
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function countByEvent(\PDO $pdo, int $eventId): int
    {
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM registrations WHERE event_id = :event_id"
        );
        $stmt->execute([':event_id' => $eventId]);
        return (int) $stmt->fetchColumn();
    }
}
