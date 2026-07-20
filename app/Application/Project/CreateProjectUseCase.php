<?php

declare(strict_types=1);

namespace App\Application\Project;

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

        $this->projectRepository->addMember($id, $userId, 'admin');

        return ['success' => true, 'message' => 'Projet créé.', 'id' => $id];
    }
}
