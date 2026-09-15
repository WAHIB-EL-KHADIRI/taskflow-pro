<?php

declare(strict_types=1);

namespace App\Application\Team;

use App\Domain\Team\Team;
use App\Domain\Team\TeamRepositoryInterface;

class CreateTeamUseCase
{
    private TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function execute(array $data, int $userId): array
    {
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
