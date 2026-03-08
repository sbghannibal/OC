// Assuming the existing method structure
public function findByGroupForGrade($groupId, $grade) {
    $query = 'SELECT * FROM event_option_items WHERE group_id = :group_id AND grade = :grade';
    $stmt = $this->pdo->prepare($query);
    // Include both parameters in the execute statement
    $stmt->execute([':group_id' => $groupId, ':grade' => $grade]);
    return $stmt->fetchAll();
}