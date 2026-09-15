<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectRepositoryInterface;
use InvalidArgumentException;

/**
 * The project-side counterpart of {@see InMemoryTeamRepository}, holding the
 * same line: `project_members`.`role` is ENUM('manager','member','viewer'),
 * so anything else is rejected instead of quietly stored.
 */
final class InMemoryProjectRepository implements ProjectRepositoryInterface
{
    /** @var array<int, array<string, mixed>> */
    private array $projects = [];

    /** @var array<int, array<int, array{id: int, role: string}>> */
    private array $members = [];

    private int $nextId = 1;

    /** @param array<string, mixed> $attributes */
    public function seedProject(int $id, array $attributes = []): void
    {
        $this->projects[$id] = $attributes + ['id' => $id, 'name' => 'Project ' . $id];
        $this->nextId = max($this->nextId, $id + 1);
    }

    public function seedMember(int $projectId, int $userId, string $role): void
    {
        $this->addMember($projectId, $userId, $role);
    }

    public function create(array $data): int
    {
        $id = $this->nextId++;
        $this->projects[$id] = $data + ['id' => $id];

        return $id;
    }

    public function addMember(int $projectId, int $userId, string $role = Project::ROLE_MEMBER): void
    {
        if (!in_array($role, Project::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Data truncated for column 'role': '%s' is not in ENUM('%s')",
                    $role,
                    implode("','", Project::ALLOWED_ROLES)
                )
            );
        }

        $this->members[$projectId][] = ['id' => $userId, 'role' => $role];
    }

    public function getMembers(int $projectId): array
    {
        return $this->members[$projectId] ?? [];
    }

    public function findById(int $id): ?array
    {
        return $this->projects[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->projects);
    }

    public function save(array $data): int
    {
        return $this->create($data);
    }

    public function delete(int $id): bool
    {
        unset($this->projects[$id]);

        return true;
    }

    public function count(): int
    {
        return count($this->projects);
    }

    public function getStats(int $projectId): array
    {
        return [];
    }

    public function getProgress(int $projectId): int
    {
        return 0;
    }

    public function getTasksByStatus(int $projectId): array
    {
        return [];
    }

    public function getByWorkspace(int $workspaceId): array
    {
        return [];
    }
}
