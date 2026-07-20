<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\TaskRepositoryInterface;

class GetTaskDetailUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function execute(int $taskId): ?array
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return null;
        }

        $task['subtasks'] = $this->taskRepository->getSubtasks($taskId);
        $task['tags'] = $this->taskRepository->getTags($taskId);
        $task['comments'] = $this->taskRepository->getComments($taskId);
        $task['attachments'] = $this->taskRepository->getAttachments($taskId);

        return $task;
    }
}
