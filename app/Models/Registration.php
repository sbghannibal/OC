<?php

declare(strict_types=1);

namespace App\Models;

final class Registration
{
    /**
     * Persist a new registration for an event.
     *
     * @param array{event_id: int, naam: string, email: string, telefoon: string, klas_id?: int|null, klas_name?: string|null, opmerking?: string|null, parent_id?: int|null, child_id?: int|null} $data
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO registrations (event_id, naam, email, telefoon, klas_id, klas_name, opmerking, parent_id, child_id)
             VALUES (:event_id, :naam, :email, :telefoon, :klas_id, :klas_name, :opmerking, :parent_id, :child_id)"
        );
        $stmt->execute([
            ':event_id'  => $data['event_id'],
            ':naam'      => $data['naam'],
            ':email'     => $data['email'],
            ':telefoon'  => $data['telefoon'],
            ':klas_id'   => $data['klas_id'] ?? null,
            ':klas_name' => $data['klas_name'] ?? null,
            ':opmerking' => $data['opmerking'] ?? null,
            ':parent_id' => $data['parent_id'] ?? null,
            ':child_id'  => $data['child_id'] ?? null,
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

    /**
     * Find a registration by child + event combination.
     * Returns any registration (including cancelled ones) so callers can upsert.
     *
     * @return array<string,mixed>|null
     */
    public static function findByChildAndEvent(\PDO $pdo, int $childId, int $eventId): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM registrations WHERE child_id = :child_id AND event_id = :event_id LIMIT 1"
        );
        $stmt->execute([':child_id' => $childId, ':event_id' => $eventId]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    /**
     * Find all active (non-cancelled) registrations for a parent for a given event.
     *
     * @return list<array<string,mixed>>
     */
    public static function findByParentAndEvent(\PDO $pdo, int $parentId, int $eventId): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM registrations
             WHERE parent_id = :parent_id AND event_id = :event_id AND cancelled_at IS NULL
             ORDER BY created_at ASC"
        );
        $stmt->execute([':parent_id' => $parentId, ':event_id' => $eventId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find a registration by ID, verifying it belongs to the given parent.
     *
     * @return array<string,mixed>|null
     */
    public static function findByIdAndParent(\PDO $pdo, int $id, int $parentId): ?array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM registrations WHERE id = :id AND parent_id = :parent_id LIMIT 1"
        );
        $stmt->execute([':id' => $id, ':parent_id' => $parentId]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    /**
     * Soft-cancel a registration by setting cancelled_at to now.
     */
    public static function cancel(\PDO $pdo, int $id): void
    {
        $stmt = $pdo->prepare(
            "UPDATE registrations SET cancelled_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    /**
     * Update mutable fields of a registration (re-activate if previously cancelled).
     *
     * @param array{naam: string, email: string, telefoon: string, opmerking?: string|null} $data
     */
    public static function updateRegistration(\PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            "UPDATE registrations
             SET naam = :naam, email = :email, telefoon = :telefoon,
                 opmerking = :opmerking, cancelled_at = NULL
             WHERE id = :id"
        );
        $stmt->execute([
            ':naam'      => $data['naam'],
            ':email'     => $data['email'],
            ':telefoon'  => $data['telefoon'],
            ':opmerking' => $data['opmerking'] ?? null,
            ':id'        => $id,
        ]);
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
