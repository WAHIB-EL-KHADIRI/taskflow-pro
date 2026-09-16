<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Project\CreateProjectUseCase;
use App\Infrastructure\Persistence\ProjectRepository\ProjectRepository;
use App\Infrastructure\Persistence\WorkspaceRepository\WorkspaceRepository;

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
        $this->requireAuth();

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            $this->fail('Le nom est obligatoire.');
        }

        $workspaceId = (int) $this->input('workspace_id', 0);
        if ($workspaceId <= 0) {
            $this->fail('Espace de travail invalide.');
        }

        // Only these columns are passed on. Database::insert() builds its
        // column list from the array keys, so handing it the request body
        // would let a caller set created_by.
        $this->finish(
            (new CreateProjectUseCase(new ProjectRepository(), new WorkspaceRepository()))->execute(
                [
                    'name' => $name,
                    'description' => $this->nullableInput('description'),
                    'color' => $this->nullableInput('color'),
                    'workspace_id' => $workspaceId,
                ],
                (int) $this->userId()
            )
        );
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

    private function nullableInput(string $key): ?string
    {
        $value = trim((string) $this->input($key, ''));

        return $value === '' ? null : $value;
    }

    /**
     * @param array<string, mixed> $result
     */
    private function finish(array $result): never
    {
        $key = ($result['success'] ?? false) === true ? 'success' : 'error';
        $this->session->flash($key, (string) ($result['message'] ?? ''));
        $this->back();
    }

    private function fail(string $message): never
    {
        $this->session->flash('error', $message);
        $this->back();
    }
}
