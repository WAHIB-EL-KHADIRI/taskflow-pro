<?php

declare(strict_types=1);

namespace App\Application\Team;

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
        $data['created_by'] = $userId;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->teamRepository->create($data);

        $this->teamRepository->addMember($id, $userId, 'admin');

        return ['success' => true, 'message' => 'Équipe créée.', 'id' => $id];
    }
}
