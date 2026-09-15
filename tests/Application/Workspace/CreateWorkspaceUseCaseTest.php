<?php

declare(strict_types=1);

namespace Tests\Application\Workspace;

use App\Application\Workspace\CreateWorkspaceUseCase;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryWorkspaceRepository;

/**
 * Defects 1-3 of #10 were all on this one insert path, and all three were
 * fixed together without a test. Their team and project equivalents are
 * covered; this closes the asymmetry, so a revert of any of the three fails
 * here instead of at the first real CREATE.
 */
final class CreateWorkspaceUseCaseTest extends TestCase
{
    private InMemoryWorkspaceRepository $workspaces;
    private CreateWorkspaceUseCase $useCase;

    protected function setUp(): void
    {
        $this->workspaces = new InMemoryWorkspaceRepository();
        $this->useCase = new CreateWorkspaceUseCase($this->workspaces);
    }

    /**
     * Defects 1 and 2: the use case set `created_by`, which `workspaces` does
     * not have, and left `owner_id` -- which is NOT NULL -- unset. The double
     * rejects both the way the driver would.
     */
    public function testTheOwnerIsWrittenAndNoColumnIsInventedForIt(): void
    {
        $result = $this->useCase->execute(['name' => 'Atlas'], 10);

        $workspace = $this->workspaces->findById((int) $result['id']);

        $this->assertTrue($result['success']);
        $this->assertNotNull($workspace);
        $this->assertSame(10, $workspace['owner_id']);
        $this->assertArrayNotHasKey('created_by', $workspace);
        $this->assertArrayHasKey('created_at', $workspace);
    }

    /**
     * Defect 1, third failure: `slug` is NOT NULL UNIQUE and nothing in the
     * codebase generated one.
     */
    public function testASlugIsGenerated(): void
    {
        $result = $this->useCase->execute(['name' => 'Atlas Platform'], 10);

        $workspace = $this->workspaces->findById((int) $result['id']);

        $this->assertNotNull($workspace);
        $this->assertIsString($workspace['slug']);
        $this->assertStringStartsWith('atlas-platform-', $workspace['slug']);
    }

    /**
     * The column is UNIQUE, so two workspaces sharing a name is a failed
     * insert rather than a cosmetic collision. Nothing stops a user from
     * picking a name someone else already used.
     */
    public function testTwoWorkspacesWithTheSameNameDoNotCollide(): void
    {
        $first = $this->useCase->execute(['name' => 'Atlas'], 10);
        $second = $this->useCase->execute(['name' => 'Atlas'], 11);

        $this->assertTrue($first['success']);
        $this->assertTrue($second['success']);
        $this->assertNotSame(
            $this->workspaces->findById((int) $first['id'])['slug'],
            $this->workspaces->findById((int) $second['id'])['slug']
        );
    }

    /**
     * A name with nothing sluggable in it still has to produce a non-empty
     * slug, because the column cannot hold ''.
     */
    public function testANameWithNoSluggableCharactersStillYieldsASlug(): void
    {
        $result = $this->useCase->execute(['name' => '???'], 10);

        $workspace = $this->workspaces->findById((int) $result['id']);

        $this->assertNotNull($workspace);
        $this->assertStringStartsWith('workspace-', $workspace['slug']);
    }

    /**
     * Defect 3: teams and projects enrolled their creator, workspaces did
     * not. UserRepository::getWorkspaces() reads through workspace_members,
     * so without this the creator could not see the workspace they had just
     * made -- with defects 1 and 2 fixed and this one left, the bug simply
     * moves from a failed insert to an invisible row.
     */
    public function testTheCreatorIsEnrolledSoTheWorkspaceIsVisibleToThem(): void
    {
        $result = $this->useCase->execute(['name' => 'Atlas'], 10);
        $id = (int) $result['id'];

        $this->assertSame([['id' => 10, 'role' => 'admin']], $this->workspaces->getMembers($id));
        $this->assertTrue($this->workspaces->userHasAccess($id, 10));
    }

    /**
     * The role written at creation has to be one `workspace_members`.`role`
     * can actually hold. This is the same drift that MemberRoleContractTest
     * guards for teams and projects, asserted here at the use-case boundary.
     */
    public function testTheRoleWrittenAtCreationIsWithinTheColumnDefinition(): void
    {
        $result = $this->useCase->execute(['name' => 'Atlas'], 10);

        // The double throws on an out-of-range ENUM value, so reaching this
        // line at all is the assertion; naming the value keeps the failure
        // message useful.
        $this->assertSame('admin', $this->workspaces->getMembers((int) $result['id'])[0]['role']);
    }
}
