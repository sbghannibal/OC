<?php

declare(strict_types=1);

namespace App\Models;

final class EventOptionGroup
{
    /** @return list<array<string,mixed>> */
    public static function findByEvent(\PDO $pdo, int $eventId): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM event_option_groups WHERE event_id = :event_id ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([':event_id' => $eventId]);
        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string,mixed>|null */
    public static function findById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM event_option_groups WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    /**
     * @param array{event_id: int, name: string, max_select: int, is_required: bool, sort_order?: int} $data
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO event_option_groups (event_id, name, max_select, is_required, sort_order)
             VALUES (:event_id, :name, :max_select, :is_required, :sort_order)"
        );
        $stmt->execute([
            ':event_id'   => $data['event_id'],
            ':name'       => $data['name'],
            ':max_select' => $data['max_select'],
            ':is_required'=> $data['is_required'] ? 1 : 0,
            ':sort_order' => $data['sort_order'] ?? 0,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array{name: string, max_select: int, is_required: bool, sort_order?: int} $data
     */
    public static function update(\PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            "UPDATE event_option_groups
             SET name = :name, max_select = :max_select, is_required = :is_required, sort_order = :sort_order
             WHERE id = :id"
        );
        $stmt->execute([
            ':name'       => $data['name'],
            ':max_select' => $data['max_select'],
            ':is_required'=> $data['is_required'] ? 1 : 0,
            ':sort_order' => $data['sort_order'] ?? 0,
            ':id'         => $id,
        ]);
    }

    public static function delete(\PDO $pdo, int $id): void
    {
        // Delete all items in group first (cascades to registration_option_items)
        $items = EventOptionItem::findByGroup($pdo, $id);
        foreach ($items as $item) {
            EventOptionItem::delete($pdo, (int) $item['id']);
        }
        $stmt = $pdo->prepare("DELETE FROM event_option_groups WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
