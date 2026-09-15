<?php

declare(strict_types=1);

namespace Tests\Application\Project;

use App\Application\Project\AddProjectMemberUseCase;
use App\Application\Project\CreateProjectUseCase;
use App\Domain\Project\Project;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryProjectRepository;

final class CreateProjectUseCaseTest extends TestCase
{
    private InMemoryProjectRepository $projects;
    private CreateProjectUseCase $useCase;

    protected function setUp(): void
    {
        $this->projects = new InMemoryProjectRepository();
        $this->useCase = new CreateProjectUseCase($this->projects);
    }

    public function testTheCreatorIsEnrolledAsManager(): void
    {
        $result = $this->useCase->execute(['name' => 'Migration', 'workspace_id' => 1], 10);

        $this->assertTrue($result['success']);
        $this->assertSame(
            [['id' => 10, 'role' => 'manager']],
            $this->projects->getMembers((int) $result['id'])
        );
    }

    public function testTheDefaultsAreApplied(): void
    {
        $result = $this->useCase->execute(['name' => 'Migration', 'workspace_id' => 1], 10);

        $project = $this->projects->findById((int) $result['id']);

        $this->assertNotNull($project);
        $this->assertSame('active', $project['status']);
        $this->assertSame('#4f46e5', $project['color']);
        $this->assertSame(10, $project['created_by']);
    }

    public function testAnExplicitStatusAndColourSurviveTheDefaults(): void
    {
        $result = $this->useCase->execute(
            ['name' => 'Migration', 'workspace_id' => 1, 'status' => 'archived', 'color' => '#000000'],
            10
        );

        $project = $this->projects->findById((int) $result['id']);

        $this->assertNotNull($project);
        $this->assertSame('archived', $project['status']);
        $this->assertSame('#000000', $project['color']);
    }

    public function testTheCreatorCanThenAddSomeoneToTheirOwnProject(): void
    {
        $created = $this->useCase->execute(['name' => 'Migration', 'workspace_id' => 1], 10);
        $projectId = (int) $created['id'];

        $result = (new AddProjectMemberUseCase($this->projects))
            ->execute(projectId: $projectId, userId: 20, actorId: 10);

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertCount(2, $this->projects->getMembers($projectId));
    }

    public function testTheRoleWrittenAtCreationIsWithinTheColumnDefinition(): void
    {
        $result = $this->useCase->execute(['name' => 'Migration', 'workspace_id' => 1], 10);

        $role = $this->projects->getMembers((int) $result['id'])[0]['role'];

        $this->assertContains($role, Project::ALLOWED_ROLES);
    }
}
