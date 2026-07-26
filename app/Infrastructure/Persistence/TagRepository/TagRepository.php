<?php

declare(strict_types=1);
namespace App\Infrastructure\Persistence\TagRepository;

use App\Domain\Tag\TagRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class TagRepository implements TagRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM tags WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM tags ORDER BY name ASC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('tags', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('tags', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('tags', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM tags");
        return (int)($r['c'] ?? 0);
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, COUNT(tt.task_id) as task_count
             FROM tags t LEFT JOIN task_tags tt ON t.id = tt.tag_id
             WHERE t.workspace_id = ?
             GROUP BY t.id ORDER BY t.name ASC",
            [$workspaceId]
        );
    }

    public function getPopular(int $workspaceId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, COUNT(tt.task_id) as task_count
             FROM tags t JOIN task_tags tt ON t.id = tt.tag_id
             WHERE t.workspace_id = ?
             GROUP BY t.id ORDER BY task_count DESC LIMIT ?",
            [$workspaceId, $limit]
        );
    }
}