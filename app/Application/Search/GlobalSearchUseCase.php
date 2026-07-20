<?php

declare(strict_types=1);

namespace App\Application\Search;

use App\Domain\Task\TaskRepositoryInterface;
use App\Domain\Project\ProjectRepositoryInterface;

class GlobalSearchUseCase
{
    private TaskRepositoryInterface $taskRepository;
    private ProjectRepositoryInterface $projectRepository;

    public function __construct(
        TaskRepositoryInterface $taskRepository,
        ProjectRepositoryInterface $projectRepository
    ) {
        $this->taskRepository = $taskRepository;
        $this->projectRepository = $projectRepository;
    }

    public function execute(string $query, int $userId): array
    {
        $trimmedQuery = trim($query);
        if ($trimmedQuery === '') {
            return ['tasks' => [], 'projects' => []];
        }

        $tasks = $this->taskRepository->search($trimmedQuery);

        $projects = $this->projectRepository->findAll();

        $filteredProjects = array_filter($projects, function ($project) use ($trimmedQuery) {
            $name = strtolower($project['name'] ?? '');
            $description = strtolower($project['description'] ?? '');
            $search = strtolower($trimmedQuery);
            return str_contains($name, $search) || str_contains($description, $search);
        });

        return [
            'tasks' => $tasks,
            'projects' => array_values($filteredProjects),
        ];
    }
}
