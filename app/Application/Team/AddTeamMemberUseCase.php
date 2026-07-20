<?php

declare(strict_types=1);

namespace App\Application\Team;

use App\Domain\Team\TeamRepositoryInterface;

class AddTeamMemberUseCase
{
    private TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function execute(int $teamId, int $userId, string $role = 'member'): array
    {
        $team = $this->teamRepository->findById($teamId);
        if (!$team) {
            return ['success' => false, 'message' => 'Équipe introuvable.'];
        }

        $members = $this->teamRepository->getMembers($teamId);
        foreach ($members as $member) {
            if ((int)$member['id'] === $userId) {
                return ['success' => false, 'message' => 'Cet utilisateur est déjà membre.'];
            }
        }

        $this->teamRepository->addMember($teamId, $userId, $role);

        return ['success' => true, 'message' => 'Membre ajouté à l\'équipe.'];
    }
}
