<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\TaskRepositoryInterface;

class SearchTasksUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function execute(string $query, ?int $projectId = null): array
    {
        $trimmedQuery = trim($query);
        if ($trimmedQuery === '') {
            return [];
        }

        return $this->taskRepository->search($trimmedQuery, $projectId);
    }
}
