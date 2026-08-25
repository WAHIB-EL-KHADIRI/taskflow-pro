<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Dashboard\GetDashboardDataUseCase;
use App\Infrastructure\Persistence\TaskRepository\TaskRepository;
use App\Infrastructure\Persistence\UserRepository\UserRepository;

class DashboardController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();

        $this->view('dashboard.index', $this->dashboardData() + ['title' => 'Tableau de bord']);
    }

    public function kanban(): void
    {
        $this->requireAuth();

        $data = $this->dashboardData();

        // The board is the same task list read by status rather than by date.
        // Grouping here keeps the template to presentation, and keeps the
        // three columns in one place so a new status cannot silently vanish
        // from the board by being absent from the markup.
        $board = ['todo' => [], 'in_progress' => [], 'done' => []];
        foreach ($data['tasks'] as $task) {
            $status = (string) ($task['status'] ?? 'todo');
            if (!array_key_exists($status, $board)) {
                $status = 'todo';
            }
            $board[$status][] = $task;
        }

        $this->view('dashboard.kanban', [
            'board' => $board,
            'title' => 'Kanban',
        ]);
    }

    public function calendar(): void
    {
        $this->requireAuth();

        $data = $this->dashboardData();

        // Tasks with no due date cannot appear on a calendar. They are counted
        // and reported rather than dropped, so the page never shows fewer
        // tasks than the dashboard without saying why.
        $byDate = [];
        $undated = 0;
        foreach ($data['tasks'] as $task) {
            $due = $task['due_date'] ?? null;
            if (!is_string($due) || $due === '') {
                $undated++;
                continue;
            }
            $byDate[$due][] = $task;
        }
        ksort($byDate);

        $this->view('dashboard.calendar', [
            'byDate' => $byDate,
            'undated' => $undated,
            'title' => 'Calendrier',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function dashboardData(): array
    {
        return (new GetDashboardDataUseCase(new UserRepository(), new TaskRepository()))
            ->execute((int) $this->userId());
    }
}
