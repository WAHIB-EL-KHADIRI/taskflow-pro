<?php

declare(strict_types=1);

namespace Tests\Application\Team;

use App\Application\Team\AddTeamMemberUseCase;
use App\Domain\Team\Team;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryTeamRepository;

final class AddTeamMemberUseCaseTest extends TestCase
{
    private InMemoryTeamRepository $teams;
    private AddTeamMemberUseCase $useCase;

    protected function setUp(): void
    {
        $this->teams = new InMemoryTeamRepository();
        $this->useCase = new AddTeamMemberUseCase($this->teams);
    }

    public function testALeadCanAddAMember(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 10);

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertSame(
            [['id' => 10, 'role' => 'lead'], ['id' => 20, 'role' => 'member']],
            $this->teams->getMembers(1)
        );
    }

    /**
     * The regression this suite exists for. The privileged role was compared
     * against 'admin', which `team_members`.`role` cannot hold, so the check
     * below could never pass and the use case was unreachable in practice --
     * for the team's own creator included.
     */
    public function testTheLeadRoleIsOneTheColumnCanActuallyHold(): void
    {
        $this->assertContains(Team::ROLE_LEAD, Team::ALLOWED_ROLES);
        $this->assertNotContains('admin', Team::ALLOWED_ROLES);
    }

    public function testAPlainMemberCannotAddAnyone(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);
        $this->teams->seedMember(1, 11, Team::ROLE_MEMBER);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 11);

        $this->assertFalse($result['success']);
        $this->assertSame('Action non autorisee.', $result['message']);
        $this->assertCount(2, $this->teams->getMembers(1));
    }

    public function testANonMemberCannotAddAnyone(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 999);

        $this->assertFalse($result['success']);
        $this->assertSame('Action non autorisee.', $result['message']);
        $this->assertCount(1, $this->teams->getMembers(1));
    }

    public function testALeadOfAnotherTeamCannotAddHere(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedTeam(2);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);
        $this->teams->seedMember(2, 77, Team::ROLE_LEAD);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 77);

        $this->assertFalse($result['success']);
        $this->assertCount(1, $this->teams->getMembers(1));
    }

    public function testAnUnknownTeamIsRejectedBeforeAnyMembershipIsRead(): void
    {
        $result = $this->useCase->execute(teamId: 404, userId: 20, actorId: 10);

        $this->assertFalse($result['success']);
        $this->assertSame('Équipe introuvable.', $result['message']);
    }

    public function testAnExistingMemberIsNotAddedTwice(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);
        $this->teams->seedMember(1, 20, Team::ROLE_MEMBER);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 10);

        $this->assertFalse($result['success']);
        $this->assertCount(2, $this->teams->getMembers(1));
    }

    /**
     * A role outside the ENUM must be refused by the use case. Without this
     * guard the value reaches the driver, where strict mode rejects the
     * insert and a non-strict server stores '' -- a member row that no
     * privilege check can ever match.
     */
    public function testARoleTheColumnCannotHoldIsRefused(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 10, role: 'superadmin');

        $this->assertFalse($result['success']);
        $this->assertSame('Role invalide.', $result['message']);
        $this->assertCount(1, $this->teams->getMembers(1));
    }

    public function testTheRoleIsCheckedBeforeAuthorisationIsEvenConsidered(): void
    {
        $result = $this->useCase->execute(teamId: 404, userId: 20, actorId: 999, role: 'owner');

        $this->assertFalse($result['success']);
        $this->assertSame('Role invalide.', $result['message']);
    }

    public function testALeadMayPromoteAnotherLead(): void
    {
        $this->teams->seedTeam(1);
        $this->teams->seedMember(1, 10, Team::ROLE_LEAD);

        $result = $this->useCase->execute(teamId: 1, userId: 20, actorId: 10, role: Team::ROLE_LEAD);

        $this->assertTrue($result['success']);
        $this->assertSame('lead', $this->teams->getMembers(1)[1]['role']);
    }
}
