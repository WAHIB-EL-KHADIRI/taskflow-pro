<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class ProjectController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('projects.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('projects.create');
    }

    public function store(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $this->view('projects.show', ['projectId' => $id]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->view('projects.edit', ['projectId' => $id]);
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
}
