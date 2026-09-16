<?php

declare(strict_types=1);

namespace App\Domain\Workspace;

class Workspace
{
    /**
     * Mirrors `workspace_members`.`role` ENUM('owner','admin','member') in
     * database/migrate.php. A value outside this set is not a weaker
     * permission -- MySQL rejects it in strict mode, and stores '' otherwise,
     * which no privilege check can ever match.
     *
     * ROLE_OWNER is defined because the column defines it, but nothing writes
     * it: `workspaces`.`owner_id` is the single source of truth for who owns
     * a workspace, and duplicating that into the membership row would give
     * two answers that can disagree.
     */
    public const ROLE_OWNER = 'owner';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_MEMBER = 'member';

    /** @var list<string> */
    public const ALLOWED_ROLES = [self::ROLE_OWNER, self::ROLE_ADMIN, self::ROLE_MEMBER];

    /** Roles a member may be given through the application. */
    public const ASSIGNABLE_ROLES = [self::ROLE_ADMIN, self::ROLE_MEMBER];

    public function __construct(
        public readonly ?int $id = null,
        public readonly string $name = '',
        public readonly string $slug = '',
        public readonly ?string $description = null,
        public readonly ?string $logo = null,
        public readonly int $ownerId = 0,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {
    }
}
