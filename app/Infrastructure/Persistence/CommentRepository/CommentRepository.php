<?php

declare(strict_types=1);
namespace App\Infrastructure\Persistence\CommentRepository;

use App\Domain\Comment\CommentRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class CommentRepository implements CommentRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM comments WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM comments ORDER BY created_at DESC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('comments', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('comments', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('comments', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM comments");
        return (int)($r['c'] ?? 0);
    }

    public function create(array $data): int
    {
        return $this->db->insert('comments', $data);
    }

    public function getTaskComments(int $taskId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.name as user_name, u.avatar
             FROM comments c JOIN users u ON c.user_id = u.id
             WHERE c.task_id = ? ORDER BY c.created_at ASC",
            [$taskId]
        );
    }

    public function getReplies(int $parentId): array
    {
        return $this->db->fetchAll(
            "SELECT c.*, u.name as user_name, u.avatar
             FROM comments c JOIN users u ON c.user_id = u.id
             WHERE c.parent_id = ? ORDER BY c.created_at ASC",
            [$parentId]
        );
    }

    public function getCountByTask(int $taskId): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM comments WHERE task_id = ?", [$taskId]);
        return (int)($r['c'] ?? 0);
    }
}