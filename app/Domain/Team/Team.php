<?php

declare(strict_types=1);

namespace App\Domain\Team;

class Team
{
    /**
     * Mirrors `team_members`.`role` ENUM('lead','member') in
     * database/migrate.php. A value outside this set is not a weaker
     * permission -- MySQL rejects it in strict mode, and stores '' otherwise,
     * which no privilege check can ever match.
     */
    public const ROLE_LEAD = 'lead';
    public const ROLE_MEMBER = 'member';

    /** @var list<string> */
    public const ALLOWED_ROLES = [self::ROLE_LEAD, self::ROLE_MEMBER];

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
    ) {
    }
}
