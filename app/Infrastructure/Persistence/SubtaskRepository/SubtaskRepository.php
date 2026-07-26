<?php

declare(strict_types=1);
namespace App\Infrastructure\Persistence\SubtaskRepository;

use App\Infrastructure\Persistence\Database;

class SubtaskRepository
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch(
            "SELECT s.*, u.name as assigned_to_name
             FROM sub_tasks s LEFT JOIN users u ON s.assigned_to = u.id
             WHERE s.id = ?",
            [$id]
        );
    }

    public function getByTask(int $taskId): array
    {
        return $this->db->fetchAll(
            "SELECT s.*, u.name as assigned_to_name FROM sub_tasks s
             LEFT JOIN users u ON s.assigned_to = u.id
             WHERE s.task_id = ? ORDER BY s.position ASC",
            [$taskId]
        );
    }

    public function create(array $data): int
    {
        return $this->db->insert('sub_tasks', $data);
    }

    public function toggleStatus(int $id, bool $completed): void
    {
        $this->db->query(
            "UPDATE sub_tasks SET completed = ?, completed_at = ? WHERE id = ?",
            [$completed ? 1 : 0, $completed ? date('Y-m-d H:i:s') : null, $id]
        );
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('sub_tasks', 'id = ?', [$id]) > 0;
    }
}