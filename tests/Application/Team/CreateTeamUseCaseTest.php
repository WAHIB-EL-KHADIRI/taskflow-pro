<?php

declare(strict_types=1);

namespace Tests\Application\Team;

use App\Application\Team\AddTeamMemberUseCase;
use App\Application\Team\CreateTeamUseCase;
use App\Domain\Team\Team;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryTeamRepository;

final class CreateTeamUseCaseTest extends TestCase
{
    private InMemoryTeamRepository $teams;
    private CreateTeamUseCase $useCase;

    protected function setUp(): void
    {
        $this->teams = new InMemoryTeamRepository();
        $this->useCase = new CreateTeamUseCase($this->teams);
    }

    public function testTheCreatorIsEnrolledAsLead(): void
    {
        $result = $this->useCase->execute(['name' => 'Platform', 'workspace_id' => 1], 10);

        $this->assertTrue($result['success']);
        $this->assertSame(
            [['id' => 10, 'role' => 'lead']],
            $this->teams->getMembers((int) $result['id'])
        );
    }

    public function testTheOwnerAndTimestampAreWritten(): void
    {
        $result = $this->useCase->execute(['name' => 'Platform', 'workspace_id' => 1], 10);

        $team = $this->teams->findById((int) $result['id']);

        $this->assertNotNull($team);
        $this->assertSame(10, $team['owner_id']);
        $this->assertArrayHasKey('created_at', $team);
        $this->assertArrayNotHasKey('created_by', $team);
    }

    /**
     * End to end, this is the failure the two use cases produced together:
     * creation stored a role the column does not define, so the creator was
     * not a lead, so the creator could not add anyone to the team they had
     * just made. Neither half looks wrong on its own.
     */
    public function testTheCreatorCanThenAddSomeoneToTheirOwnTeam(): void
    {
        $created = $this->useCase->execute(['name' => 'Platform', 'workspace_id' => 1], 10);
        $teamId = (int) $created['id'];

        $result = (new AddTeamMemberUseCase($this->teams))
            ->execute(teamId: $teamId, userId: 20, actorId: 10);

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertCount(2, $this->teams->getMembers($teamId));
    }

    public function testTheRoleWrittenAtCreationIsWithinTheColumnDefinition(): void
    {
        $result = $this->useCase->execute(['name' => 'Platform', 'workspace_id' => 1], 10);

        $role = $this->teams->getMembers((int) $result['id'])[0]['role'];

        $this->assertContains($role, Team::ALLOWED_ROLES);
    }
}
