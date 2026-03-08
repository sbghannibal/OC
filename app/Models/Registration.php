<?php

declare(strict_types=1);

namespace App\Models;

final class Registration
{
    public const PAYMENT_UNKNOWN = 'unknown';
    public const PAYMENT_PAID    = 'paid';
    public const PAYMENT_UNPAID  = 'unpaid';

    /** @var list<string> */
    public const PAYMENT_STATUSES = [self::PAYMENT_UNKNOWN, self::PAYMENT_PAID, self::PAYMENT_UNPAID];

    /**
     * @param array{event_id: int, naam: string, email: string, telefoon?: string, opmerking?: string} $data
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO registrations (event_id, naam, email, telefoon, opmerking)
             VALUES (:event_id, :naam, :email, :telefoon, :opmerking)"
        );
        $stmt->execute([
            ':event_id'  => $data['event_id'],
            ':naam'      => $data['naam'],
            ':email'     => $data['email'],
            ':telefoon'  => $data['telefoon'] ?? null,
            ':opmerking' => $data['opmerking'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public static function allForEvent(\PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM registrations WHERE event_id = :event_id ORDER BY created_at ASC"
        );
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Update the payment status for a single registration.
     * Only allows known status values.
     */
    public static function updatePaymentStatus(\PDO $pdo, int $id, string $status): void
    {
        if (!in_array($status, self::PAYMENT_STATUSES, true)) {
            throw new \InvalidArgumentException("Onbekende betaalstatus: {$status}");
        }
        $stmt = $pdo->prepare(
            "UPDATE registrations SET payment_status = :status WHERE id = :id"
        );
        $stmt->execute([':status' => $status, ':id' => $id]);
    }
}
