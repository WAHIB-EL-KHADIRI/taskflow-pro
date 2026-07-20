<?php
declare(strict_types=1);
namespace App\Domain\Comment;

use App\Domain\Shared\RepositoryInterface;

interface CommentRepositoryInterface extends RepositoryInterface
{
    public function create(array $data): int;
    public function getTaskComments(int $taskId): array;
    public function getReplies(int $parentId): array;
    public function getCountByTask(int $taskId): int;
}
