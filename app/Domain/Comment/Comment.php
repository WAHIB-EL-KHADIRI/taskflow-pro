<?php
declare(strict_types=1);
namespace App\Domain\Comment;

class Comment
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $content = '',
        public readonly int $taskId = 0,
        public readonly int $userId = 0,
        public readonly ?int $parentId = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
}
