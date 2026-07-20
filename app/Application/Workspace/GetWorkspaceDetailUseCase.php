<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Workspace\WorkspaceRepositoryInterface;
use App\Domain\Project\ProjectRepositoryInterface;

class GetWorkspaceDetailUseCase
{
    private WorkspaceRepositoryInterface $workspaceRepository;
    private ProjectRepositoryInterface $projectRepository;

    public function __construct(
        WorkspaceRepositoryInterface $workspaceRepository,
        ProjectRepositoryInterface $projectRepository
    ) {
        $this->workspaceRepository = $workspaceRepository;
        $this->projectRepository = $projectRepository;
    }

    public function execute(int $workspaceId): ?array
    {
        $workspace = $this->workspaceRepository->findById($workspaceId);
        if (!$workspace) {
            return null;
        }

        $workspace['projects'] = $this->projectRepository->getByWorkspace($workspaceId);
        $workspace['members'] = $this->workspaceRepository->getMembers($workspaceId);

        return $workspace;
    }
}
