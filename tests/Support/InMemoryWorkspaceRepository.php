<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Workspace\WorkspaceRepositoryInterface;
use InvalidArgumentException;

/**
 * Stands in for WorkspaceRepository without a database, and keeps the four
 * guarantees MySQL gives that a plain mock would not, because all four are
 * what the defects in #10 actually tripped over:
 *
 *   - `workspaces` has a fixed column list, and Database::insert() builds its
 *     INSERT straight from the array keys -- so an unknown key such as
 *     `created_by` is a rejected statement, not an ignored entry;
 *   - `owner_id` is NOT NULL;
 *   - `slug` is NOT NULL UNIQUE;
 *   - `workspace_members`.`role` is an ENUM, so a value outside it is an
 *     error rather than a stored string.
 *
 * A double that accepted anything would have reported the broken code as
 * working -- which is how the broken code survived in the first place.
 */
final class InMemoryWorkspaceRepository implements WorkspaceRepositoryInterface
{
    /**
     * The column list of `workspaces` in database/migrate.php.
     *
     * @var list<string>
     */
    private const COLUMNS = [
        'id',
        'name',
        'slug',
        'description',
        'logo',
        'owner_id',
        'created_at',
        'updated_at',
    ];

    /** @var list<string> The ENUM on `workspace_members`.`role`. */
    private const ALLOWED_ROLES = ['owner', 'admin', 'member'];

    /** @var array<int, array<string, mixed>> */
    private array $workspaces = [];

    /** @var array<int, array<int, array{id: int, role: string}>> */
    private array $members = [];

    private int $nextId = 1;

    public function create(array $data): int
    {
        $unknown = array_diff(array_keys($data), self::COLUMNS);
        if ($unknown !== []) {
            throw new InvalidArgumentException(
                sprintf(
                    "SQLSTATE[42S22]: Unknown column '%s' in 'field list'",
                    (string) reset($unknown)
                )
            );
        }

        foreach (['owner_id', 'slug'] as $required) {
            if (!isset($data[$required]) || $data[$required] === '') {
                throw new InvalidArgumentException(
                    sprintf("SQLSTATE[23000]: Column '%s' cannot be null", $required)
                );
            }
        }

        foreach ($this->workspaces as $existing) {
            if (($existing['slug'] ?? null) === $data['slug']) {
                throw new InvalidArgumentException(
                    sprintf(
                        "SQLSTATE[23000]: Duplicate entry '%s' for key 'slug'",
                        (string) $data['slug']
                    )
                );
            }
        }

        $id = $this->nextId++;
        $this->workspaces[$id] = $data + ['id' => $id];

        return $id;
    }

    public function addMember(int $workspaceId, int $userId, string $role = 'member'): void
    {
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    "Data truncated for column 'role': '%s' is not in ENUM('%s')",
                    $role,
                    implode("','", self::ALLOWED_ROLES)
                )
            );
        }

        $this->members[$workspaceId][] = ['id' => $userId, 'role' => $role];
    }

    public function removeMember(int $workspaceId, int $userId): void
    {
        $this->members[$workspaceId] = array_values(
            array_filter(
                $this->members[$workspaceId] ?? [],
                static fn (array $member): bool => $member['id'] !== $userId
            )
        );
    }

    public function getMembers(int $workspaceId): array
    {
        return $this->members[$workspaceId] ?? [];
    }

    public function getMemberCount(int $workspaceId): int
    {
        return count($this->getMembers($workspaceId));
    }

    public function userHasAccess(int $workspaceId, int $userId): bool
    {
        foreach ($this->getMembers($workspaceId) as $member) {
            if ($member['id'] === $userId) {
                return true;
            }
        }

        return false;
    }

    public function findById(int $id): ?array
    {
        return $this->workspaces[$id] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->workspaces);
    }

    public function save(array $data): int
    {
        return $this->create($data);
    }

    public function delete(int $id): bool
    {
        unset($this->workspaces[$id]);

        return true;
    }

    public function count(): int
    {
        return count($this->workspaces);
    }
}
