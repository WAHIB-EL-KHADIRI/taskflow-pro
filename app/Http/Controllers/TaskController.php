<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class TaskController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('tasks.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('tasks.create');
    }

    public function store(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $this->view('tasks.show', ['taskId' => $id]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->view('tasks.edit', ['taskId' => $id]);
    }

    public function update(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function destroy(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function updateStatus(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function updatePosition(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function addComment(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function addSubtask(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function toggleSubtask(string $id, string $sid): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }
}
