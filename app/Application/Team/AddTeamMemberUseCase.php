<?php

declare(strict_types=1);

namespace App\Application\Team;

use App\Domain\Team\Team;
use App\Domain\Team\TeamRepositoryInterface;

class AddTeamMemberUseCase
{
    private TeamRepositoryInterface $teamRepository;

    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param int $actorId The user making the request. Required, and not
     *                     defaulted: membership is an authorization decision,
     *                     and a default would let a caller omit it silently.
     */
    public function execute(
        int $teamId,
        int $userId,
        int $actorId,
        string $role = Team::ROLE_MEMBER
    ): array {
        // Reject a role the column cannot hold before it reaches the
        // driver: an out-of-range ENUM value is stored as '' when strict
        // mode is off, which reads back as a member nobody can promote.
        if (!in_array($role, Team::ALLOWED_ROLES, true)) {
            return ['success' => false, 'message' => 'Role invalide.'];
        }

        $team = $this->teamRepository->findById($teamId);
        if (!$team) {
            return ['success' => false, 'message' => 'Équipe introuvable.'];
        }

        $members = $this->teamRepository->getMembers($teamId);

        // Nothing here previously considered who was asking. The route
        // carries AuthMiddleware, which answers "is this someone", not
        // "may this someone change this membership" -- so any logged-in
        // user could have added any account, at any role, to any team.
        if (!$this->isLead($members, $actorId)) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        foreach ($members as $member) {
            if ((int)$member['id'] === $userId) {
                return ['success' => false, 'message' => 'Cet utilisateur est déjà membre.'];
            }
        }

        $this->teamRepository->addMember($teamId, $userId, $role);

        return ['success' => true, 'message' => 'Membre ajouté à l\'équipe.'];
    }

    /**
     * @param array<int, array<string, mixed>> $members
     */
    private function isLead(array $members, int $actorId): bool
    {
        foreach ($members as $member) {
            if ((int) ($member['id'] ?? 0) === $actorId) {
                return ($member['role'] ?? '') === Team::ROLE_LEAD;
            }
        }

        return false;
    }
}
