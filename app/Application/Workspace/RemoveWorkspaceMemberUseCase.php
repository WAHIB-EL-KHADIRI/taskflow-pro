<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Workspace\Workspace;
use App\Domain\Workspace\WorkspaceRepositoryInterface;

class RemoveWorkspaceMemberUseCase
{
    private WorkspaceRepositoryInterface $workspaceRepository;

    public function __construct(WorkspaceRepositoryInterface $workspaceRepository)
    {
        $this->workspaceRepository = $workspaceRepository;
    }

    /**
     * @param int $actorId The user making the request. Required, and not
     *                     defaulted: membership is an authorization decision,
     *                     and a default would let a caller omit it silently.
     *
     * @return array<string, mixed>
     */
    public function execute(int $workspaceId, int $userId, int $actorId): array
    {
        $workspace = $this->workspaceRepository->findById($workspaceId);
        if (!$workspace) {
            return ['success' => false, 'message' => 'Espace de travail introuvable.'];
        }

        $members = $this->workspaceRepository->getMembers($workspaceId);

        if (!$this->isAdmin($members, $actorId)) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        // The owner is read from `workspaces`.`owner_id`, not from a
        // membership role. An admin may remove another admin -- that is a
        // legitimate administrative act -- but removing the owner would leave
        // a workspace whose owner_id points at a non-member, invisible on its
        // own owner's dashboard and administrable by whoever remained.
        if ((int) ($workspace['owner_id'] ?? 0) === $userId) {
            return [
                'success' => false,
                'message' => 'Le proprietaire ne peut pas etre retire.',
            ];
        }

        if (!$this->isMember($members, $userId)) {
            return ['success' => false, 'message' => 'Cet utilisateur n\'est pas membre.'];
        }

        $this->workspaceRepository->removeMember($workspaceId, $userId);

        return ['success' => true, 'message' => 'Membre retire de l\'espace de travail.'];
    }

    /**
     * @param array<int, array<string, mixed>> $members
     */
    private function isAdmin(array $members, int $actorId): bool
    {
        foreach ($members as $member) {
            if ((int) ($member['id'] ?? 0) === $actorId) {
                return ($member['role'] ?? '') === Workspace::ROLE_ADMIN;
            }
        }

        return false;
    }

    /**
     * @param array<int, array<string, mixed>> $members
     */
    private function isMember(array $members, int $userId): bool
    {
        foreach ($members as $member) {
            if ((int) ($member['id'] ?? 0) === $userId) {
                return true;
            }
        }

        return false;
    }
}
