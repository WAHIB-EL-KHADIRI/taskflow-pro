<?php
declare(strict_types=1);
namespace App\Infrastructure\Persistence\ProjectRepository;

use App\Domain\Project\ProjectRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class ProjectRepository implements ProjectRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM projects WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM projects ORDER BY name ASC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('projects', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('projects', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('projects', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM projects");
        return (int)($r['c'] ?? 0);
    }

    public function create(array $data): int
    {
        return $this->db->insert('projects', [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#4f46e5',
            'status' => $data['status'] ?? 'active',
            'workspace_id' => $data['workspace_id'],
            'created_by' => $data['created_by'],
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
        ]);
    }

    public function addMember(int $projectId, int $userId, string $role = 'member'): void
    {
        $this->db->insert('project_members', [
            'project_id' => $projectId,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }

    public function getMembers(int $projectId): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.avatar, pm.role
             FROM project_members pm
             JOIN users u ON pm.user_id = u.id
             WHERE pm.project_id = ?",
            [$projectId]
        );
    }

    public function getStats(int $projectId): array
    {
        $stats = $this->db->fetch(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'todo' THEN 1 ELSE 0 END) as todo,
                    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'done' THEN 1 ELSE 0 END) as done
             FROM tasks WHERE project_id = ?",
            [$projectId]
        );
        return $stats ?: ['total' => 0, 'todo' => 0, 'in_progress' => 0, 'done' => 0];
    }

    public function getProgress(int $projectId): int
    {
        $stats = $this->getStats($projectId);
        if ($stats['total'] === 0) return 0;
        return (int)round(($stats['done'] / $stats['total']) * 100);
    }

    public function getTasksByStatus(int $projectId): array
    {
        return $this->db->fetchAll(
            "SELECT status, COUNT(*) as count FROM tasks WHERE project_id = ? GROUP BY status",
            [$projectId]
        );
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return $this->db->fetchAll("SELECT * FROM projects WHERE workspace_id = ? ORDER BY name ASC", [$workspaceId]);
    }
}
