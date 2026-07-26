<?php

declare(strict_types=1);

namespace App\Domain\Task;

use App\Domain\Shared\RepositoryInterface;

interface TaskRepositoryInterface extends RepositoryInterface
{
    public function create(array $data): int;
    public function createSubtask(int $taskId, string $title, int $position, int $createdBy): int;
    public function toggleSubtask(int $subtaskId, bool $completed): void;
    public function findByProject(int $projectId): array;
    public function getByStatus(int $projectId, string $status): array;
    public function updatePosition(int $id, int $position): void;
    public function updateStatus(int $id, string $status): void;
    public function getSubtasks(int $taskId): array;
    public function attachTag(int $taskId, int $tagId): void;
    public function detachTag(int $taskId, int $tagId): void;
    public function getTags(int $taskId): array;
    public function getComments(int $taskId): array;
    public function getAttachments(int $taskId): array;
    public function search(string $query, ?int $projectId = null): array;
    public function getOverdue(int $userId): int;
    public function getByDateRange(int $projectId, string $start, string $end): array;
}
