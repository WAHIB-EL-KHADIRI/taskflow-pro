<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Domain\Project\ProjectRepositoryInterface;

class AddProjectMemberUseCase
{
    private ProjectRepositoryInterface $projectRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    /**
     * @param int $actorId The user making the request. Required, and not
     *                     defaulted: membership is an authorization decision,
     *                     and a default would let a caller omit it silently.
     */
    public function execute(
        int $projectId,
        int $userId,
        int $actorId,
        string $role = 'member'
    ): array {
        $project = $this->projectRepository->findById($projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Projet introuvable.'];
        }

        $members = $this->projectRepository->getMembers($projectId);

        // Nothing here previously considered who was asking. The route
        // carries AuthMiddleware, which answers "is this someone", not
        // "may this someone change this membership" -- so any logged-in
        // user could have added any account, at any role, to any project.
        if (!$this->isAdmin($members, $actorId)) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        foreach ($members as $member) {
            if ((int)$member['id'] === $userId) {
                return ['success' => false, 'message' => 'Cet utilisateur est déjà membre.'];
            }
        }

        $this->projectRepository->addMember($projectId, $userId, $role);

        return ['success' => true, 'message' => 'Membre ajouté.'];
    }

    /**
     * @param array<int, array<string, mixed>> $members
     */
    private function isAdmin(array $members, int $actorId): bool
    {
        foreach ($members as $member) {
            if ((int) ($member['id'] ?? 0) === $actorId) {
                return ($member['role'] ?? '') === 'admin';
            }
        }

        return false;
    }
}
