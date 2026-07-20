<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\TaskRepositoryInterface;

class AddSubtaskUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function execute(int $taskId, string $title, int $userId): array
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return ['success' => false, 'message' => 'Tâche introuvable.'];
        }

        $trimmedTitle = trim($title);
        if ($trimmedTitle === '') {
            return ['success' => false, 'message' => 'Le titre ne peut pas être vide.'];
        }

        $existing = $this->taskRepository->getSubtasks($taskId);
        $position = count($existing);

        $id = $this->taskRepository->createSubtask($taskId, $trimmedTitle, $position, $userId);

        return ['success' => true, 'message' => 'Sous-tâche ajoutée.', 'id' => $id];
    }
}
