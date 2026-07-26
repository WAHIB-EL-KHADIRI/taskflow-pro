<?php

declare(strict_types=1);
namespace App\Domain\Tag;

class Tag
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly string $color = '#6b7280',
        public readonly int $workspaceId = 0,
        public readonly ?string $createdAt = null,
    ) {}
}