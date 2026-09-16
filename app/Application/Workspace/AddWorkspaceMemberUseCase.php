<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Workspace\Workspace;
use App\Domain\Workspace\WorkspaceRepositoryInterface;

class AddWorkspaceMemberUseCase
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
    public function execute(
        int $workspaceId,
        int $userId,
        int $actorId,
        string $role = Workspace::ROLE_MEMBER
    ): array {
        // Reject a role the column cannot hold before it reaches the driver:
        // an out-of-range ENUM value is stored as '' when strict mode is off,
        // which reads back as a member nobody can promote. ROLE_OWNER is
        // excluded even though the column defines it -- ownership lives in
        // `workspaces`.`owner_id`, not in a membership row.
        if (!in_array($role, Workspace::ASSIGNABLE_ROLES, true)) {
            return ['success' => false, 'message' => 'Role invalide.'];
        }

        $workspace = $this->workspaceRepository->findById($workspaceId);
        if (!$workspace) {
            return ['success' => false, 'message' => 'Espace de travail introuvable.'];
        }

        $members = $this->workspaceRepository->getMembers($workspaceId);

        // The route carries AuthMiddleware, which answers "is this someone",
        // not "may this someone change this membership".
        if (!$this->isAdmin($members, $actorId)) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        foreach ($members as $member) {
            if ((int) ($member['id'] ?? 0) === $userId) {
                return ['success' => false, 'message' => 'Cet utilisateur est deja membre.'];
            }
        }

        $this->workspaceRepository->addMember($workspaceId, $userId, $role);

        return ['success' => true, 'message' => 'Membre ajoute a l\'espace de travail.'];
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
}
