<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class TeamController extends AbstractController
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('teams.index');
    }

    public function create(): void
    {
        $this->requireAuth();
        $this->view('teams.create');
    }

    public function store(): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }

    public function show(string $id): void
    {
        $this->requireAuth();
        $this->view('teams.show', ['teamId' => $id]);
    }

    public function addMember(string $id): void
    {
        http_response_code(501);
        echo 'Not implemented yet';
    }
}
