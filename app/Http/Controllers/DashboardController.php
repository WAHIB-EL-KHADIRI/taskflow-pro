<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class DashboardController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('dashboard.index');
    }

    public function kanban(): void
    {
        $this->requireAuth();
        $this->view('dashboard.kanban');
    }

    public function calendar(): void
    {
        $this->requireAuth();
        $this->view('dashboard.calendar');
    }
}
