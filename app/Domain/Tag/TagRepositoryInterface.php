<?php

declare(strict_types=1);

namespace App\Domain\Tag;

use App\Domain\Shared\RepositoryInterface;

interface TagRepositoryInterface extends RepositoryInterface
{
    public function getByWorkspace(int $workspaceId): array;
    public function getPopular(int $workspaceId, int $limit = 10): array;
}
