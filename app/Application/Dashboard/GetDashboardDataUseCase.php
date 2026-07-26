<?php

declare(strict_types=1);

namespace App\Application\Dashboard;

use App\Domain\User\UserRepositoryInterface;
use App\Domain\Task\TaskRepositoryInterface;

class GetDashboardDataUseCase
{
    private UserRepositoryInterface $userRepository;
    private TaskRepositoryInterface $taskRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TaskRepositoryInterface $taskRepository
    ) {
        $this->userRepository = $userRepository;
        $this->taskRepository = $taskRepository;
    }

    public function execute(int $userId): array
    {
        $projects = $this->userRepository->getProjects($userId);
        $tasks = $this->userRepository->getTasks($userId, 10);
        $teams = $this->userRepository->getTeams($userId);
        $workspaces = $this->userRepository->getWorkspaces($userId);
        $recentActivity = $this->userRepository->getRecentActivity($userId, 10);
        $overdueCount = $this->taskRepository->getOverdue($userId);

        $totalProjects = count($projects);
        $totalTasks = count($tasks);
        $totalTeams = count($teams);
        $completedTasks = 0;
        foreach ($tasks as $task) {
            if (($task['status'] ?? '') === 'done') {
                $completedTasks++;
            }
        }

        return [
            'projects' => $projects,
            'tasks' => $tasks,
            'teams' => $teams,
            'workspaces' => $workspaces,
            'recentActivity' => $recentActivity,
            'stats' => [
                'totalProjects' => $totalProjects,
                'totalTasks' => $totalTasks,
                'completedTasks' => $completedTasks,
                'overdueTasks' => $overdueCount,
                'totalTeams' => $totalTeams,
            ],
        ];
    }
}
