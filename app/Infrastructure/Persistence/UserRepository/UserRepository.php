<?php
declare(strict_types=1);
namespace App\Infrastructure\Persistence\UserRepository;

use App\Domain\User\UserRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class UserRepository implements UserRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->fetch("SELECT * FROM users WHERE email = ?", [$email]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
    }

    public function create(array $data): int
    {
        return $this->db->insert('users', $data);
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('users', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('users', $data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->db->update('users', $data, 'id = ?', [$id]) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('users', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM users");
        return (int)($r['c'] ?? 0);
    }

    public function getProjects(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT p.*, pm.role FROM projects p
             JOIN project_members pm ON p.id = pm.project_id
             WHERE pm.user_id = ? ORDER BY p.created_at DESC",
            [$userId]
        );
    }

    public function getTeams(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, tm.role FROM teams t
             JOIN team_members tm ON t.id = tm.team_id
             WHERE tm.user_id = ?",
            [$userId]
        );
    }

    public function getWorkspaces(int $userId): array
    {
        return $this->db->fetchAll(
            "SELECT w.*, wm.role FROM workspaces w
             JOIN workspace_members wm ON w.id = wm.workspace_id
             WHERE wm.user_id = ? ORDER BY w.created_at DESC",
            [$userId]
        );
    }

    public function getTasks(int $userId, int $limit = 20): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, p.name as project_name FROM tasks t
             LEFT JOIN projects p ON t.project_id = p.id
             WHERE t.assigned_to = ? OR t.created_by = ?
             ORDER BY t.created_at DESC LIMIT ?",
            [$userId, $userId, $limit]
        );
    }

    public function getRecentActivity(int $userId, int $limit = 10): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM activity_log WHERE user_id = ?
             ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function updateLastLogin(int $id): void
    {
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
    }
}
