<?php

declare(strict_types=1);

namespace App\Models;

final class Registration
{
    /**
     * Persist a new registration for an event.
     *
     * @param array{event_id: int, naam: string, email: string, telefoon: string, opmerking?: string} $data
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
            ':telefoon' => $data['telefoon'],
            ':opmerking'=> $data['opmerking'] ?? null,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public static function findById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
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

    /**
     * Update payment information for a registration.
     *
     * @param array{payment_status: string, paid_at: string|null, payment_note: string|null} $data
     */
    public static function updatePayment(\PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            "UPDATE registrations
             SET payment_status = :payment_status,
                 paid_at        = :paid_at,
                 payment_note   = :payment_note
             WHERE id = :id"
        );
        $stmt->execute([
            ':payment_status' => $data['payment_status'],
            ':paid_at'        => $data['paid_at'],
            ':payment_note'   => $data['payment_note'],
            ':id'             => $id,
        ]);
    }

    /**
     * Validate a Belgian mobile phone number.
     *
     * Accepted formats (after stripping spaces, dots, dashes, slashes, parentheses):
     *   - 04xxxxxxxx        (local, 10 digits)
     *   - +324xxxxxxxx      (E.164 with +)
     *   - 00324xxxxxxxx     (E.164 with 0032)
     */
    public static function validateBelgianMobile(string $phone): bool
    {
        $normalized = preg_replace('/[\s.\-()\\/]/', '', $phone);
        if ($normalized === null || $normalized === '') {
            return false;
        }
        // Match +324xxxxxxxx, 00324xxxxxxxx, or 04xxxxxxxx
        return (bool) preg_match('/^(\+324[0-9]{8}|00324[0-9]{8}|04[0-9]{8})$/', $normalized);
    }
}
