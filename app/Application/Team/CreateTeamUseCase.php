<?php

declare(strict_types=1);

namespace App\Application\Team;

use App\Domain\Team\Team;
use App\Domain\Team\TeamRepositoryInterface;
use App\Domain\Workspace\WorkspaceRepositoryInterface;

class CreateTeamUseCase
{
    private TeamRepositoryInterface $teamRepository;
    private ?WorkspaceRepositoryInterface $workspaceRepository;

    /**
     * @param WorkspaceRepositoryInterface|null $workspaceRepository Used to
     *        check that the caller belongs to the workspace they are creating
     *        in. Nullable only so existing callers keep working; when it is
     *        null the check is skipped, so every HTTP path must pass it.
     */
    public function __construct(
        TeamRepositoryInterface $teamRepository,
        ?WorkspaceRepositoryInterface $workspaceRepository = null
    ) {
        $this->teamRepository = $teamRepository;
        $this->workspaceRepository = $workspaceRepository;
    }

    public function execute(array $data, int $userId): array
    {
        $workspaceId = (int) ($data['workspace_id'] ?? 0);
        if ($workspaceId <= 0) {
            return ['success' => false, 'message' => 'Espace de travail invalide.'];
        }

        // workspace_id arrives in the request body. Without this a logged-in
        // user could post any id and create a team inside someone else's
        // workspace -- the same shape of hole as #10 item 4, one level up.
        if (
            $this->workspaceRepository !== null
            && !$this->workspaceRepository->userHasAccess($workspaceId, $userId)
        ) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        // `teams` has no `created_by` column -- it has `owner_id`, and both it
        // and `workspace_id` are NOT NULL. Writing the wrong key made
        // Database::insert() emit a column MySQL rejects, so this use case
        // could never have inserted a row.
        $data['owner_id'] = $userId;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->teamRepository->create($data);

        // 'admin' is not a member role for a team: the column is
        // ENUM('lead','member'), so the creator was stored as '' (or the
        // insert was rejected) and never matched the privilege check in
        // AddTeamMemberUseCase -- which made that use case unusable.
        $this->teamRepository->addMember($id, $userId, Team::ROLE_LEAD);

        return ['success' => true, 'message' => 'Équipe créée.', 'id' => $id];
    }
}
