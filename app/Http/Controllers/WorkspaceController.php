<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Workspace\AddWorkspaceMemberUseCase;
use App\Application\Workspace\CreateWorkspaceUseCase;
use App\Application\Workspace\RemoveWorkspaceMemberUseCase;
use App\Application\Workspace\UpdateWorkspaceUseCase;
use App\Domain\Workspace\Workspace;
use App\Infrastructure\Persistence\WorkspaceRepository\WorkspaceRepository;

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
        $this->requireAuth();

        $name = trim((string) $this->input('name', ''));
        if ($name === '') {
            $this->fail('Le nom est obligatoire.');
        }

        $this->finish((new CreateWorkspaceUseCase(new WorkspaceRepository()))->execute(
            [
                'name' => $name,
                'description' => $this->nullableInput('description'),
                'logo' => $this->nullableInput('logo'),
            ],
            (int) $this->userId()
        ));
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
        $this->requireAuth();

        $this->finish((new UpdateWorkspaceUseCase(new WorkspaceRepository()))->execute(
            (int) $id,
            [
                'name' => trim((string) $this->input('name', '')),
                'description' => $this->nullableInput('description'),
                'logo' => $this->nullableInput('logo'),
            ],
            (int) $this->userId()
        ));
    }

    public function addMember(string $id): void
    {
        $this->requireAuth();

        $userId = (int) $this->input('user_id', 0);
        if ($userId <= 0) {
            $this->fail('Utilisateur invalide.');
        }

        // Defaulted here rather than in the use case signature so that an
        // absent field is a member, not a role the caller chose.
        $role = (string) $this->input('role', Workspace::ROLE_MEMBER);

        $this->finish((new AddWorkspaceMemberUseCase(new WorkspaceRepository()))->execute(
            workspaceId: (int) $id,
            userId: $userId,
            actorId: (int) $this->userId(),
            role: $role
        ));
    }

    public function removeMember(string $id): void
    {
        $this->requireAuth();

        $userId = (int) $this->input('user_id', 0);
        if ($userId <= 0) {
            $this->fail('Utilisateur invalide.');
        }

        $this->finish((new RemoveWorkspaceMemberUseCase(new WorkspaceRepository()))->execute(
            workspaceId: (int) $id,
            userId: $userId,
            actorId: (int) $this->userId()
        ));
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
