<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\TaskRepositoryInterface;

class UpdateTaskStatusUseCase
{
    private TaskRepositoryInterface $taskRepository;

    private const VALID_STATUSES = ['todo', 'in_progress', 'review', 'done'];

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function execute(int $taskId, string $status): array
    {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            return ['success' => false, 'message' => 'Statut invalide.'];
        }

        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return ['success' => false, 'message' => 'Tâche introuvable.'];
        }

        $this->taskRepository->updateStatus($taskId, $status);

        return ['success' => true, 'message' => 'Statut mis à jour.'];
    }
}
