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

    public function execute(int $projectId, int $userId, string $role = 'member'): array
    {
        $project = $this->projectRepository->findById($projectId);
        if (!$project) {
            return ['success' => false, 'message' => 'Projet introuvable.'];
        }

        $members = $this->projectRepository->getMembers($projectId);
        foreach ($members as $member) {
            if ((int)$member['id'] === $userId) {
                return ['success' => false, 'message' => 'Cet utilisateur est déjà membre.'];
            }
        }

        $this->projectRepository->addMember($projectId, $userId, $role);

        return ['success' => true, 'message' => 'Membre ajouté.'];
    }
}
