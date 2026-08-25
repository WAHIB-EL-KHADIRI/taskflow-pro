<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Workspace\WorkspaceRepositoryInterface;

class CreateWorkspaceUseCase
{
    private WorkspaceRepositoryInterface $workspaceRepository;

    public function __construct(WorkspaceRepositoryInterface $workspaceRepository)
    {
        $this->workspaceRepository = $workspaceRepository;
    }

    public function execute(array $data, int $userId): array
    {
        // `workspaces` has no `created_by`: it has `owner_id NOT NULL`, and
        // `slug VARCHAR(200) NOT NULL UNIQUE`. Neither was being set, and
        // Database::insert() passes keys through unfiltered, so this insert
        // failed three ways at once and had never been run.
        $data['owner_id'] = $userId;
        $data['slug'] = $this->slugFor((string) ($data['name'] ?? ''));
        $data['created_at'] = date('Y-m-d H:i:s');

        $id = $this->workspaceRepository->create($data);

        // CreateTeamUseCase and CreateProjectUseCase both do this; this one
        // did not. UserRepository::getWorkspaces() reads through
        // workspace_members, so without it the creator could not see the
        // workspace they had just made.
        $this->workspaceRepository->addMember($id, $userId, 'admin');

        return ['success' => true, 'message' => 'Espace de travail créé.', 'id' => $id];
    }

    /**
     * The slug column is unique, so a collision is a failed insert rather than
     * a cosmetic problem. A random suffix is cheaper and more predictable here
     * than a query-retry loop, and the slug is not user-facing.
     */
    private function slugFor(string $name): string
    {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'workspace';
        }

        return substr($slug, 0, 180) . '-' . bin2hex(random_bytes(4));
    }
}
