<?php

declare(strict_types=1);

namespace App\Domain\Task;

class Task
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $title = '',
        public readonly ?string $description = null,
        public readonly string $status = 'todo',
        public readonly string $priority = 'medium',
        public readonly ?string $dueDate = null,
        public readonly ?string $finishedAt = null,
        public readonly int $position = 0,
        public readonly int $projectId = 0,
        public readonly ?int $assignedTo = null,
        public readonly int $createdBy = 0,
        public readonly ?string $repeatInterval = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }
}
