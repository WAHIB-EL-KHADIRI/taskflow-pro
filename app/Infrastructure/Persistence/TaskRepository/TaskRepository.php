<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\TaskRepository;

use App\Domain\Task\TaskRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class TaskRepository implements TaskRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM tasks WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM tasks ORDER BY created_at DESC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('tasks', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('tasks', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('tasks', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM tasks");
        return (int)($r['c'] ?? 0);
    }

    public function create(array $data): int
    {
        return $this->db->insert('tasks', $data);
    }

    public function findByProject(int $projectId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, u.name as assigned_to_name
             FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.project_id = ? ORDER BY t.priority ASC, t.due_date ASC",
            [$projectId]
        );
    }

    public function getByStatus(int $projectId, string $status): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, u.name as assigned_to_name
             FROM tasks t LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.project_id = ? AND t.status = ?
             ORDER BY t.position ASC",
            [$projectId, $status]
        );
    }

    public function updatePosition(int $id, int $position): void
    {
        $this->db->query("UPDATE tasks SET position = ? WHERE id = ?", [$position, $id]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->db->query("UPDATE tasks SET status = ? WHERE id = ?", [$status, $id]);
    }

    public function getSubtasks(int $taskId): array
    {
        return $this->db->fetchAll("SELECT * FROM sub_tasks WHERE task_id = ? ORDER BY position ASC", [$taskId]);
    }

    public function createSubtask(int $taskId, string $title, int $position, int $createdBy): int
    {
        return $this->db->insert('sub_tasks', [
            'task_id' => $taskId,
            'title' => $title,
            'position' => $position,
            'completed' => 0,
            'created_by' => $createdBy,
        ]);
    }

    public function toggleSubtask(int $subtaskId, bool $completed): void
    {
        $this->db->query(
            "UPDATE sub_tasks SET completed = ?, completed_at = ? WHERE id = ?",
            [$completed ? 1 : 0, $completed ? date('Y-m-d H:i:s') : null, $subtaskId]
        );
    }

    public function attachTag(int $taskId, int $tagId): void
    {
        $this->db->query("INSERT IGNORE INTO task_tags (task_id, tag_id) VALUES (?, ?)", [$taskId, $tagId]);
    }

    public function detachTag(int $taskId, int $tagId): void
    {
        $this->db->query("DELETE FROM task_tags WHERE task_id = ? AND tag_id = ?", [$taskId, $tagId]);
    }

    public function getTags(int $taskId): array
    {
        return $this->db->fetchAll(
            "SELECT t.* FROM tags t JOIN task_tags tt ON t.id = tt.tag_id WHERE tt.task_id = ?",
            [$taskId]
        );
    }

    public function getComments(int $taskId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.name as user_name, u.avatar
             FROM comments c JOIN users u ON c.user_id = u.id
             WHERE c.task_id = ? ORDER BY c.created_at ASC",
            [$taskId]
        );
    }

    public function getAttachments(int $taskId): array
    {
        return $this->db->fetchAll("SELECT * FROM task_attachments WHERE task_id = ?", [$taskId]);
    }

    public function search(string $query, ?int $projectId = null): array
    {
        $params = ["%{$query}%", "%{$query}%"];
        $sql = "SELECT t.*, u.name as assigned_to_name FROM tasks t
                LEFT JOIN users u ON t.assigned_to = u.id
                WHERE (t.title LIKE ? OR t.description LIKE ?)";
        if ($projectId) {
            $sql .= " AND t.project_id = ?";
            $params[] = $projectId;
        }
        $sql .= " ORDER BY t.created_at DESC";
        return $this->db->fetchAll($sql, $params);
    }

    public function getOverdue(int $userId): int
    {
        $r = $this->db->fetch(
            "SELECT COUNT(*) as c FROM tasks
             WHERE (assigned_to = ? OR created_by = ?) AND due_date < CURDATE() AND status != 'done'",
            [$userId, $userId]
        );
        return (int)($r['c'] ?? 0);
    }

    public function getByDateRange(int $projectId, string $start, string $end): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, u.name as assigned_to_name FROM tasks t
             LEFT JOIN users u ON t.assigned_to = u.id
             WHERE t.project_id = ? AND t.due_date BETWEEN ? AND ?
             ORDER BY t.due_date ASC",
            [$projectId, $start, $end]
        );
    }
}
