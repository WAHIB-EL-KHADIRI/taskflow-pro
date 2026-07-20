<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class WorkspaceController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('workspaces.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('workspaces.create');
    }

    public function store(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $this->view('workspaces.show', ['workspaceId' => $id]);
    }

    public function edit(string $id): void
    {
        $this->requireAuth();
        $this->view('workspaces.edit', ['workspaceId' => $id]);
    }

    public function update(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function addMember(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function removeMember(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }
}
