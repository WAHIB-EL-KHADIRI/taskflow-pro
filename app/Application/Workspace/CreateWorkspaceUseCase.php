<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Workspace\WorkspaceRepositoryInterface;

class CreateWorkspaceUseCase
{
    private WorkspaceRepositoryInterface $workspaceRepository;

    public function __construct(WorkspaceRepositoryInterface $workspaceRepository)
    {
        $this->workspaceRepository = $workspaceRepository;
    }

    public function execute(array $data, int $userId): array
    {
        $data['created_by'] = $userId;
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->workspaceRepository->create($data);

        return ['success' => true, 'message' => 'Espace de travail créé.', 'id' => $id];
    }
}
