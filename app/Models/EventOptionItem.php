<?php

declare(strict_types=1);

namespace App\Models;

final class EventOptionItem
{
    /** @return list<array<string,mixed>> */
    public static function findByGroup(\PDO $pdo, int $groupId): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM event_option_items WHERE group_id = :group_id ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([':group_id' => $groupId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Find items for a group that are visible to the given grade.
     *
     * @return list<array<string,mixed>>
     */
    public static function findByGroupForGrade(\PDO $pdo, int $groupId, int $grade): array
    {
        $stmt = $pdo->prepare(
            "SELECT * FROM event_option_items
             WHERE group_id = :group_id AND min_grade <= :grade_min AND max_grade >= :grade_max
             ORDER BY sort_order ASC, id ASC"
        );
        $stmt->execute([':group_id' => $groupId, ':grade_min' => $grade, ':grade_max' => $grade]);
        return $stmt->fetchAll() ?: [];
    }

    /** @return array<string,mixed>|null */
    public static function findById(\PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare("SELECT * FROM event_option_items WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return ($row !== false) ? $row : null;
    }

    /**
     * @param array{group_id: int, name: string, min_grade: int, max_grade: int, sort_order?: int, price?: float} $data
     */
    public static function create(\PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            "INSERT INTO event_option_items (group_id, name, min_grade, max_grade, sort_order, price)
             VALUES (:group_id, :name, :min_grade, :max_grade, :sort_order, :price)"
        );
        $stmt->execute([
            ':group_id'  => $data['group_id'],
            ':name'      => $data['name'],
            ':min_grade' => $data['min_grade'],
            ':max_grade' => $data['max_grade'],
            ':sort_order'=> $data['sort_order'] ?? 0,
            ':price'     => $data['price'] ?? 0.00,
        ]);
        return (int) $pdo->lastInsertId();
    }

    /**
     * @param array{name: string, min_grade: int, max_grade: int, sort_order?: int, price?: float} $data
     */
    public static function update(\PDO $pdo, int $id, array $data): void
    {
        $stmt = $pdo->prepare(
            "UPDATE event_option_items
             SET name = :name, min_grade = :min_grade, max_grade = :max_grade,
                 sort_order = :sort_order, price = :price
             WHERE id = :id"
        );
        $stmt->execute([
            ':name'      => $data['name'],
            ':min_grade' => $data['min_grade'],
            ':max_grade' => $data['max_grade'],
            ':sort_order'=> $data['sort_order'] ?? 0,
            ':price'     => $data['price'] ?? 0.00,
            ':id'        => $id,
        ]);
    }

    public static function delete(\PDO $pdo, int $id): void
    {
        // Remove from any registrations first
        $stmt = $pdo->prepare("DELETE FROM registration_option_items WHERE item_id = :id");
        $stmt->execute([':id' => $id]);

        $stmt = $pdo->prepare("DELETE FROM event_option_items WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * Return all item IDs chosen by a registration.
     *
     * @return list<int>
     */
    public static function findIdsByRegistration(\PDO $pdo, int $registrationId): array
    {
        $stmt = $pdo->prepare(
            "SELECT item_id FROM registration_option_items WHERE registration_id = :rid"
        );
        $stmt->execute([':rid' => $registrationId]);
        return array_map('intval', $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }

    /**
     * Return items with their group name and price for a registration (for display).
     *
     * @return list<array<string,mixed>>
     */
    public static function findChosenForRegistration(\PDO $pdo, int $registrationId): array
    {
        $stmt = $pdo->prepare(
            "SELECT i.name AS item_name, g.name AS group_name, i.price
             FROM registration_option_items roi
             JOIN event_option_items i ON i.id = roi.item_id
             JOIN event_option_groups g ON g.id = i.group_id
             WHERE roi.registration_id = :rid
             ORDER BY g.sort_order ASC, i.sort_order ASC"
        );
        $stmt->execute([':rid' => $registrationId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Persist the chosen items for a registration (replace all existing).
     *
     * @param list<int> $itemIds
     */
    public static function setForRegistration(\PDO $pdo, int $registrationId, array $itemIds): void
    {
        $stmt = $pdo->prepare("DELETE FROM registration_option_items WHERE registration_id = :rid");
        $stmt->execute([':rid' => $registrationId]);

        if (empty($itemIds)) {
            return;
        }
        // Single multi-row INSERT for efficiency
        $rows  = array_fill(0, count($itemIds), '(?, ?)');
        $sql   = 'INSERT INTO registration_option_items (registration_id, item_id) VALUES ' . implode(', ', $rows);
        $binds = [];
        foreach ($itemIds as $iid) {
            $binds[] = $registrationId;
            $binds[] = $iid;
        }
        $pdo->prepare($sql)->execute($binds);
    }
}
