<?php
declare(strict_types=1);
namespace App\Domain\Workspace;

use App\Domain\Shared\RepositoryInterface;

interface WorkspaceRepositoryInterface extends RepositoryInterface
{
    public function create(array $data): int;
    public function addMember(int $workspaceId, int $userId, string $role = 'member'): void;
    public function removeMember(int $workspaceId, int $userId): void;
    public function getMembers(int $workspaceId): array;
    public function getMemberCount(int $workspaceId): int;
    public function userHasAccess(int $workspaceId, int $userId): bool;
}
