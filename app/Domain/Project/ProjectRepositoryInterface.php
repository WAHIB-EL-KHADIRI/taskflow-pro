<?php

declare(strict_types=1);
namespace App\Domain\Project;

use App\Domain\Shared\RepositoryInterface;

interface ProjectRepositoryInterface extends RepositoryInterface
{
    public function create(array $data): int;
    public function addMember(int $projectId, int $userId, string $role = 'member'): void;
    public function getMembers(int $projectId): array;
    public function getStats(int $projectId): array;
    public function getProgress(int $projectId): int;
    public function getTasksByStatus(int $projectId): array;
    public function getByWorkspace(int $workspaceId): array;
}