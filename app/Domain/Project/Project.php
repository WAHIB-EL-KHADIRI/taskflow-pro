<?php

declare(strict_types=1);

namespace App\Domain\Project;

class Project
{
    /**
     * Mirrors `project_members`.`role` ENUM('manager','member','viewer') in
     * database/migrate.php. A value outside this set is not a weaker
     * permission -- MySQL rejects it in strict mode, and stores '' otherwise,
     * which no privilege check can ever match.
     */
    public const ROLE_MANAGER = 'manager';
    public const ROLE_MEMBER = 'member';
    public const ROLE_VIEWER = 'viewer';

    /** @var list<string> */
    public const ALLOWED_ROLES = [self::ROLE_MANAGER, self::ROLE_MEMBER, self::ROLE_VIEWER];

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
    ) {
    }
}
