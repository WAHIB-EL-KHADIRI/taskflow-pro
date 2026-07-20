<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Shared\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?array;
    public function create(array $data): int;
    public function updateLastLogin(int $id): void;
    public function getProjects(int $userId): array;
    public function getTeams(int $userId): array;
    public function getWorkspaces(int $userId): array;
    public function getTasks(int $userId, int $limit = 20): array;
    public function getRecentActivity(int $userId, int $limit = 10): array;
}
