<?php

declare(strict_types=1);
namespace App\Domain\Team;

use App\Domain\Shared\RepositoryInterface;

interface TeamRepositoryInterface extends RepositoryInterface
{
    public function create(array $data): int;
    public function getByWorkspace(int $workspaceId): array;
    public function transferProject(int $teamId, int $projectId): void;
    public function getProjects(int $teamId): array;
    public function addMember(int $teamId, int $userId, string $role = 'member'): void;
    public function getMembers(int $teamId): array;
}