<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Domain\Project\Project;
use App\Domain\Project\ProjectRepositoryInterface;

class CreateProjectUseCase
{
    private ProjectRepositoryInterface $projectRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function execute(array $data, int $userId): array
    {
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
