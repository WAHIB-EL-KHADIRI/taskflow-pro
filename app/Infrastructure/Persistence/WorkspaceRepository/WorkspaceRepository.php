<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\WorkspaceRepository;

use App\Domain\Workspace\WorkspaceRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class WorkspaceRepository implements WorkspaceRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM workspaces WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM workspaces ORDER BY name ASC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('workspaces', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('workspaces', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('workspaces', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM workspaces");
        return (int)($r['c'] ?? 0);
    }

    public function create(array $data): int
    {
        return $this->db->insert('workspaces', $data);
    }

    public function addMember(int $workspaceId, int $userId, string $role = 'member'): void
    {
        $this->db->insert('workspace_members', [
            'workspace_id' => $workspaceId,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }

    public function removeMember(int $workspaceId, int $userId): void
    {
        $this->db->delete('workspace_members', 'workspace_id = ? AND user_id = ?', [$workspaceId, $userId]);
    }

    public function getMembers(int $workspaceId): array
    {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.email, u.avatar, wm.role, wm.joined_at
             FROM workspace_members wm
             JOIN users u ON wm.user_id = u.id
             WHERE wm.workspace_id = ? ORDER BY wm.joined_at ASC",
            [$workspaceId]
        );
    }

    public function getMemberCount(int $workspaceId): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM workspace_members WHERE workspace_id = ?", [$workspaceId]);
        return (int)($r['c'] ?? 0);
    }

    public function userHasAccess(int $workspaceId, int $userId): bool
    {
        $r = $this->db->fetch("SELECT id FROM workspace_members WHERE workspace_id = ? AND user_id = ?", [$workspaceId, $userId]);
        return $r !== null;
    }
}
