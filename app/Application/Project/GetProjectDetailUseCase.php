<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Domain\Project\ProjectRepositoryInterface;
use App\Domain\Task\TaskRepositoryInterface;

class GetProjectDetailUseCase
{
    private ProjectRepositoryInterface $projectRepository;
    private TaskRepositoryInterface $taskRepository;

    public function __construct(
        ProjectRepositoryInterface $projectRepository,
        TaskRepositoryInterface $taskRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->taskRepository = $taskRepository;
    }

    public function execute(int $projectId): ?array
    {
        $project = $this->projectRepository->findById($projectId);
        if (!$project) {
            return null;
        }

        $project['tasks'] = $this->taskRepository->findByProject($projectId);
        $project['members'] = $this->projectRepository->getMembers($projectId);
        $project['stats'] = $this->projectRepository->getStats($projectId);
        $project['progress'] = $this->projectRepository->getProgress($projectId);

        return $project;
    }
}
