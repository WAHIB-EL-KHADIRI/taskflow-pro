<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Comment\CommentRepositoryInterface;

class AddCommentUseCase
{
    private TaskRepositoryInterface $taskRepository;
    private CommentRepositoryInterface $commentRepository;

    public function __construct(
        TaskRepositoryInterface $taskRepository,
        CommentRepositoryInterface $commentRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->commentRepository = $commentRepository;
    }

    public function execute(int $taskId, string $content, int $userId): array
    {
        $task = $this->taskRepository->findById($taskId);
        if (!$task) {
            return ['success' => false, 'message' => 'Tâche introuvable.'];
        }

        $trimmedContent = trim($content);
        if ($trimmedContent === '') {
            return ['success' => false, 'message' => 'Le commentaire ne peut pas être vide.'];
        }

        $id = $this->commentRepository->create([
            'task_id' => $taskId,
            'user_id' => $userId,
            'content' => $trimmedContent,
        ]);

        return ['success' => true, 'message' => 'Commentaire ajouté.', 'id' => $id];
    }
}
