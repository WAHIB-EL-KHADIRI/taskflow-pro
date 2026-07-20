<?php

declare(strict_types=1);

namespace App\Application\Comment;

use App\Domain\Comment\CommentRepositoryInterface;

class DeleteCommentUseCase
{
    private CommentRepositoryInterface $commentRepository;

    public function __construct(CommentRepositoryInterface $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }

    public function execute(int $commentId, int $userId): array
    {
        $comment = $this->commentRepository->findById($commentId);
        if (!$comment) {
            return ['success' => false, 'message' => 'Commentaire introuvable.'];
        }

        if ((int)$comment['user_id'] !== $userId) {
            return ['success' => false, 'message' => 'Non autorisé.'];
        }

        $this->commentRepository->delete($commentId);

        return ['success' => true, 'message' => 'Commentaire supprimé.'];
    }
}
