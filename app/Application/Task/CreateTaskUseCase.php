<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\TaskRepositoryInterface;

class CreateTaskUseCase
{
    private TaskRepositoryInterface $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function execute(array $data, int $userId): array
    {
        $data['created_by'] = $userId;
        $data['status'] = $data['status'] ?? 'todo';
        $data['priority'] = $data['priority'] ?? 'moyenne';
        $data['position'] = $data['position'] ?? 0;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->taskRepository->create($data);

        return ['success' => true, 'message' => 'Tâche créée.', 'id' => $id];
    }
}
