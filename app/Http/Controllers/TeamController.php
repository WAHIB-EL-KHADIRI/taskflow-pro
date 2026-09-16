<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Application\Team\AddTeamMemberUseCase;
use App\Application\Team\CreateTeamUseCase;
use App\Domain\Team\Team;
use App\Infrastructure\Persistence\TeamRepository\TeamRepository;
use App\Infrastructure\Persistence\WorkspaceRepository\WorkspaceRepository;

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
        // would let a caller set owner_id or is_default.
        $this->finish(
            (new CreateTeamUseCase(new TeamRepository(), new WorkspaceRepository()))->execute(
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
        $this->view('teams.show', ['teamId' => $id]);
    }

    public function addMember(string $id): void
    {
        $this->requireAuth();

        $userId = (int) $this->input('user_id', 0);
        if ($userId <= 0) {
            $this->fail('Utilisateur invalide.');
        }

        // Defaulted here rather than in the use case call so an absent field
        // is a member, not a role the caller chose.
        $role = (string) $this->input('role', Team::ROLE_MEMBER);

        $this->finish((new AddTeamMemberUseCase(new TeamRepository()))->execute(
            teamId: (int) $id,
            userId: $userId,
            actorId: (int) $this->userId(),
            role: $role
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
