<?php

declare(strict_types=1);
namespace App\Domain\Project;

class Project
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly string $color = '#4f46e5',
        public readonly string $status = 'active',
        public readonly int $workspaceId = 0,
        public readonly ?int $teamId = null,
        public readonly int $createdBy = 0,
        public readonly ?string $startDate = null,
        public readonly ?string $endDate = null,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}
}