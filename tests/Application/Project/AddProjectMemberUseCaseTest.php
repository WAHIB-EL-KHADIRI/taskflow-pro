<?php

declare(strict_types=1);

namespace Tests\Application\Project;

use App\Application\Project\AddProjectMemberUseCase;
use App\Domain\Project\Project;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryProjectRepository;

final class AddProjectMemberUseCaseTest extends TestCase
{
    private InMemoryProjectRepository $projects;
    private AddProjectMemberUseCase $useCase;

    protected function setUp(): void
    {
        $this->projects = new InMemoryProjectRepository();
        $this->useCase = new AddProjectMemberUseCase($this->projects);
    }

    public function testAManagerCanAddAMember(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);

        $result = $this->useCase->execute(projectId: 1, userId: 20, actorId: 10);

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertSame(
            [['id' => 10, 'role' => 'manager'], ['id' => 20, 'role' => 'member']],
            $this->projects->getMembers(1)
        );
    }

    public function testTheManagerRoleIsOneTheColumnCanActuallyHold(): void
    {
        $this->assertContains(Project::ROLE_MANAGER, Project::ALLOWED_ROLES);
        $this->assertNotContains('admin', Project::ALLOWED_ROLES);
    }

    public function testAPlainMemberCannotAddAnyone(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);
        $this->projects->seedMember(1, 11, Project::ROLE_MEMBER);

        $result = $this->useCase->execute(projectId: 1, userId: 20, actorId: 11);

        $this->assertFalse($result['success']);
        $this->assertSame('Action non autorisee.', $result['message']);
        $this->assertCount(2, $this->projects->getMembers(1));
    }

    public function testAViewerCannotAddAnyone(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);
        $this->projects->seedMember(1, 12, Project::ROLE_VIEWER);

        $result = $this->useCase->execute(projectId: 1, userId: 20, actorId: 12);

        $this->assertFalse($result['success']);
        $this->assertCount(2, $this->projects->getMembers(1));
    }

    public function testANonMemberCannotAddAnyone(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);

        $result = $this->useCase->execute(projectId: 1, userId: 20, actorId: 999);

        $this->assertFalse($result['success']);
        $this->assertCount(1, $this->projects->getMembers(1));
    }

    public function testAnUnknownProjectIsRejected(): void
    {
        $result = $this->useCase->execute(projectId: 404, userId: 20, actorId: 10);

        $this->assertFalse($result['success']);
        $this->assertSame('Projet introuvable.', $result['message']);
    }

    public function testAnExistingMemberIsNotAddedTwice(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);
        $this->projects->seedMember(1, 20, Project::ROLE_MEMBER);

        $result = $this->useCase->execute(projectId: 1, userId: 20, actorId: 10);

        $this->assertFalse($result['success']);
        $this->assertCount(2, $this->projects->getMembers(1));
    }

    public function testARoleTheColumnCannotHoldIsRefused(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);

        $result = $this->useCase->execute(projectId: 1, userId: 20, actorId: 10, role: 'admin');

        $this->assertFalse($result['success']);
        $this->assertSame('Role invalide.', $result['message']);
        $this->assertCount(1, $this->projects->getMembers(1));
    }

    public function testAViewerMayBeAddedByAManager(): void
    {
        $this->projects->seedProject(1);
        $this->projects->seedMember(1, 10, Project::ROLE_MANAGER);

        $result = $this->useCase->execute(
            projectId: 1,
            userId: 20,
            actorId: 10,
            role: Project::ROLE_VIEWER
        );

        $this->assertTrue($result['success']);
        $this->assertSame('viewer', $this->projects->getMembers(1)[1]['role']);
    }
}
