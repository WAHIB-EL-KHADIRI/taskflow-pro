<?php

declare(strict_types=1);
namespace App\Infrastructure\Persistence\TeamRepository;

use App\Domain\Team\TeamRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class TeamRepository implements TeamRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM teams WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM teams ORDER BY name ASC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('teams', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('teams', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('teams', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM teams");
        return (int)($r['c'] ?? 0);
    }

    public function create(array $data): int
    {
        return $this->db->insert('teams', $data);
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return $this->db->fetchAll(
            "SELECT t.*, COUNT(tm.user_id) as member_count
             FROM teams t
             LEFT JOIN team_members tm ON t.id = tm.team_id
             WHERE t.workspace_id = ?
             GROUP BY t.id ORDER BY t.name ASC",
            [$workspaceId]
        );
    }

    public function transferProject(int $teamId, int $projectId): void
    {
        $this->db->query("UPDATE projects SET team_id = ? WHERE id = ?", [$teamId, $projectId]);
    }

    public function getProjects(int $teamId): array
    {
        return $this->db->fetchAll("SELECT * FROM projects WHERE team_id = ? ORDER BY name ASC", [$teamId]);
    }

    public function addMember(int $teamId, int $userId, string $role = 'member'): void
    {
        $this->db->insert('team_members', [
            'team_id' => $teamId,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }

    public function getMembers(int $teamId): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.avatar, tm.role
             FROM team_members tm
             JOIN users u ON tm.user_id = u.id
             WHERE tm.team_id = ?",
            [$teamId]
        );
    }
}