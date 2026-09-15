<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Team\Team;
use App\Domain\Team\TeamRepositoryInterface;
use InvalidArgumentException;

/**
 * Stands in for TeamRepository without a database, and keeps the one
 * guarantee MySQL gives that a plain mock would not: `team_members`.`role`
 * is an ENUM, so a value outside it is an error rather than a stored string.
 *
 * That is deliberate. The defect this suite covers was a role the column
 * could never hold, and a test double that accepts any string would have
 * reported the broken code as working.
 */
final class InMemoryTeamRepository implements TeamRepositoryInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $teams = [];

    /** @var array<int, array<int, array{id: int, role: string}>> */
    private array $members = [];

    private int $nextId = 1;

    /** @param array<string, mixed> $attributes */
    public function seedTeam(int $id, array $attributes = []): void
    {
        $this->teams[$id] = $attributes + ['id' => $id, 'name' => 'Team ' . $id];
        $this->nextId = max($this->nextId, $id + 1);
    }

    public function seedMember(int $teamId, int $userId, string $role): void
    {
        $this->addMember($teamId, $userId, $role);
    }

    public function create(array $data): int
    {
        $id = $this->nextId++;
        $this->teams[$id] = $data + ['id' => $id];

        return $id;
    }

    public function addMember(int $teamId, int $userId, string $role = Team::ROLE_MEMBER): void
    {
        if (!in_array($role, Team::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Data truncated for column 'role': '%s' is not in ENUM('%s')",
                    $role,
                    implode("','", Team::ALLOWED_ROLES)
                )
            );
        }

        $this->members[$teamId][] = ['id' => $userId, 'role' => $role];
    }

    public function getMembers(int $teamId): array
    {
        return $this->members[$teamId] ?? [];
    }

    public function findById(int $id): ?array
    {
        return $this->teams[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->teams);
    }

    public function save(array $data): int
    {
        return $this->create($data);
    }

    public function delete(int $id): bool
    {
        unset($this->teams[$id]);

        return true;
    }

    public function count(): int
    {
        return count($this->teams);
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return [];
    }

    public function transferProject(int $teamId, int $projectId): void
    {
    }

    public function getProjects(int $teamId): array
    {
        return [];
    }
}
