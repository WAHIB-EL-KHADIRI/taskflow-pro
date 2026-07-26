<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

class Workspace
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly string $slug = '',
        public readonly ?string $description = null,
        public readonly ?string $logo = null,
        public readonly int $ownerId = 0,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
}
