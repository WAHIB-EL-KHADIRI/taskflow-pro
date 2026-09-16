<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectRepositoryInterface;
use App\Domain\Workspace\WorkspaceRepositoryInterface;

class CreateProjectUseCase
{
    private ProjectRepositoryInterface $projectRepository;
    private ?WorkspaceRepositoryInterface $workspaceRepository;

    /**
     * @param WorkspaceRepositoryInterface|null $workspaceRepository Used to
     *        check that the caller belongs to the workspace they are creating
     *        in. Nullable only so existing callers keep working; when it is
     *        null the check is skipped, so every HTTP path must pass it.
     */
    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        ?WorkspaceRepositoryInterface $workspaceRepository = null
    ) {
        $this->projectRepository = $projectRepository;
        $this->workspaceRepository = $workspaceRepository;
    }

    public function execute(array $data, int $userId): array
    {
        $workspaceId = (int) ($data['workspace_id'] ?? 0);
        if ($workspaceId <= 0) {
            return ['success' => false, 'message' => 'Espace de travail invalide.'];
        }

        // workspace_id arrives in the request body. Without this a logged-in
        // user could post any id and create a project inside someone else's
        // workspace.
        if (
            $this->workspaceRepository !== null
            && !$this->workspaceRepository->userHasAccess($workspaceId, $userId)
        ) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        $data['created_by'] = $userId;
        $data['status'] = $data['status'] ?? 'active';
        $data['color'] = $data['color'] ?? '#4f46e5';
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->projectRepository->create($data);

        // 'admin' is not a member role for a project: the column is
        // ENUM('manager','member','viewer'), so the creator was stored as
        // '' (or the insert was rejected) and never matched the privilege
        // check in AddProjectMemberUseCase.
        $this->projectRepository->addMember($id, $userId, Project::ROLE_MANAGER);

        return ['success' => true, 'message' => 'Projet créé.', 'id' => $id];
    }
}
