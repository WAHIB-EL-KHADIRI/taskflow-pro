<?php

declare(strict_types=1);

namespace App\Application\Workspace;

use App\Domain\Workspace\Workspace;
use App\Domain\Workspace\WorkspaceRepositoryInterface;

class UpdateWorkspaceUseCase
{
    private WorkspaceRepositoryInterface $workspaceRepository;

    public function __construct(WorkspaceRepositoryInterface $workspaceRepository)
    {
        $this->workspaceRepository = $workspaceRepository;
    }

    /**
     * @param array<string, mixed> $data
     * @param int $actorId The user making the request. Required, and not
     *                     defaulted: editing a workspace is an authorization
     *                     decision, and a default would let a caller omit it
     *                     silently.
     *
     * @return array<string, mixed>
     */
    public function execute(int $workspaceId, array $data, int $actorId): array
    {
        $workspace = $this->workspaceRepository->findById($workspaceId);
        if (!$workspace) {
            return ['success' => false, 'message' => 'Espace de travail introuvable.'];
        }

        if (!$this->isAdmin($workspaceId, $actorId)) {
            return ['success' => false, 'message' => 'Action non autorisee.'];
        }

        $name = trim((string) ($data['name'] ?? ''));
        if ($name === '') {
            return ['success' => false, 'message' => 'Le nom est obligatoire.'];
        }

        // Only these three columns are writable, listed explicitly rather than
        // passed through: Database::update() builds its SET clause from the
        // array keys, so an unfiltered array lets the caller write owner_id or
        // slug. `slug` in particular is deliberately absent -- it is UNIQUE,
        // generated once at creation, and other rows reference the workspace
        // by it, so a rename must not invalidate it.
        $this->workspaceRepository->save([
            'id' => $workspaceId,
            'name' => $name,
            'description' => $this->nullableText($data['description'] ?? null),
            'logo' => $this->nullableText($data['logo'] ?? null),
        ]);

        return ['success' => true, 'message' => 'Espace de travail mis a jour.'];
    }

    private function nullableText(mixed $value): ?string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? null : $text;
    }

    private function isAdmin(int $workspaceId, int $actorId): bool
    {
        foreach ($this->workspaceRepository->getMembers($workspaceId) as $member) {
            if ((int) ($member['id'] ?? 0) === $actorId) {
                return ($member['role'] ?? '') === Workspace::ROLE_ADMIN;
            }
        }

        return false;
    }
}
