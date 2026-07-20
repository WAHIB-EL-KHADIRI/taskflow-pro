<?php
declare(strict_types=1);
namespace App\Domain\Team;

class Team
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly ?string $color = '#6366f1',
        public readonly int $workspaceId = 0,
        public readonly int $ownerId = 0,
        public readonly bool $isDefault = false,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
}
