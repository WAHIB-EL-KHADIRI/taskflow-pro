<?php

declare(strict_types=1);

namespace Tests\Application;

use App\Application\Project\CreateProjectUseCase;
use App\Application\Team\CreateTeamUseCase;
use App\Application\Workspace\CreateWorkspaceUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryProjectRepository;
use Tests\Support\InMemoryTeamRepository;
use Tests\Support\InMemoryWorkspaceRepository;

/**
 * `workspace_id` arrives in the request body of POST /teams/create and
 * POST /projects/create. Both routes carry AuthMiddleware, which answers
 * "is this someone", not "may this someone create things in that workspace" --
 * so without a check any logged-in user could post an arbitrary id and create
 * a team or project inside a workspace they have never been a member of.
 *
 * This is the same shape of hole as #10 item 4, one level up.
 */
final class WorkspaceScopedCreationTest extends TestCase
{
    private const MEMBER = 10;
    private const OUTSIDER = 99;

    private InMemoryWorkspaceRepository $workspaces;
    private int $workspaceId;

    protected function setUp(): void
    {
        $this->workspaces = new InMemoryWorkspaceRepository();

        $created = (new CreateWorkspaceUseCase($this->workspaces))
            ->execute(['name' => 'Atlas'], self::MEMBER);
        $this->workspaceId = (int) $created['id'];
    }

    // --- teams ------------------------------------------------------------

    public function testAMemberCanCreateATeamInTheirWorkspace(): void
    {
        $teams = new InMemoryTeamRepository();

        $result = (new CreateTeamUseCase($teams, $this->workspaces))->execute(
            ['name' => 'Platform', 'workspace_id' => $this->workspaceId],
            self::MEMBER
        );

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertSame(1, $teams->count());
    }

    public function testANonMemberCannotCreateATeamInSomeoneElsesWorkspace(): void
    {
        $teams = new InMemoryTeamRepository();

        $result = (new CreateTeamUseCase($teams, $this->workspaces))->execute(
            ['name' => 'Hijacked', 'workspace_id' => $this->workspaceId],
            self::OUTSIDER
        );

        $this->assertFalse($result['success']);
        $this->assertSame(0, $teams->count());
    }

    public function testATeamNeedsAWorkspaceId(): void
    {
        $teams = new InMemoryTeamRepository();

        $result = (new CreateTeamUseCase($teams, $this->workspaces))
            ->execute(['name' => 'Platform'], self::MEMBER);

        $this->assertFalse($result['success']);
        $this->assertSame(0, $teams->count());
    }

    // --- projects ---------------------------------------------------------

    public function testAMemberCanCreateAProjectInTheirWorkspace(): void
    {
        $projects = new InMemoryProjectRepository();

        $result = (new CreateProjectUseCase($projects, $this->workspaces))->execute(
            ['name' => 'Migration', 'workspace_id' => $this->workspaceId],
            self::MEMBER
        );

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertSame(1, $projects->count());
    }

    public function testANonMemberCannotCreateAProjectInSomeoneElsesWorkspace(): void
    {
        $projects = new InMemoryProjectRepository();

        $result = (new CreateProjectUseCase($projects, $this->workspaces))->execute(
            ['name' => 'Hijacked', 'workspace_id' => $this->workspaceId],
            self::OUTSIDER
        );

        $this->assertFalse($result['success']);
        $this->assertSame(0, $projects->count());
    }

    public function testAProjectNeedsAWorkspaceId(): void
    {
        $projects = new InMemoryProjectRepository();

        $result = (new CreateProjectUseCase($projects, $this->workspaces))
            ->execute(['name' => 'Migration'], self::MEMBER);

        $this->assertFalse($result['success']);
        $this->assertSame(0, $projects->count());
    }

    // --- the compatibility seam -------------------------------------------

    /**
     * The workspace repository is optional so the existing callers and their
     * suites keep working. That means an omitted repository silently skips
     * the check, which is exactly the failure mode worth pinning: if someone
     * wires a new HTTP path and forgets the second argument, this test is the
     * reminder that the guard is opt-in.
     */
    public function testWithoutTheWorkspaceRepositoryTheCheckIsSkipped(): void
    {
        $teams = new InMemoryTeamRepository();

        $result = (new CreateTeamUseCase($teams))->execute(
            ['name' => 'Platform', 'workspace_id' => $this->workspaceId],
            self::OUTSIDER
        );

        $this->assertTrue($result['success'], 'guard is opt-in by construction');
        $this->assertSame(1, $teams->count());
    }
}
