<?php

declare(strict_types=1);

namespace Tests\Application\Workspace;

use App\Application\Workspace\AddWorkspaceMemberUseCase;
use App\Application\Workspace\CreateWorkspaceUseCase;
use App\Application\Workspace\RemoveWorkspaceMemberUseCase;
use App\Application\Workspace\UpdateWorkspaceUseCase;
use App\Domain\Workspace\Workspace;
use PHPUnit\Framework\TestCase;
use Tests\Support\InMemoryWorkspaceRepository;

/**
 * The three workspace administration paths, which were 501 stubs until now
 * (#18). The negative cases are the reason this layer exists: the routes carry
 * AuthMiddleware, which answers "is this someone", not "may this someone
 * change this workspace" -- the same gap that made #10 item 4 a broken access
 * control on teams.
 */
final class WorkspaceAdministrationTest extends TestCase
{
    private const OWNER = 10;
    private const SECOND_ADMIN = 11;
    private const PLAIN_MEMBER = 12;
    private const OUTSIDER = 99;

    private InMemoryWorkspaceRepository $workspaces;
    private int $workspaceId;

    protected function setUp(): void
    {
        $this->workspaces = new InMemoryWorkspaceRepository();

        $created = (new CreateWorkspaceUseCase($this->workspaces))
            ->execute(['name' => 'Atlas'], self::OWNER);
        $this->workspaceId = (int) $created['id'];

        $this->workspaces->addMember($this->workspaceId, self::SECOND_ADMIN, Workspace::ROLE_ADMIN);
        $this->workspaces->addMember($this->workspaceId, self::PLAIN_MEMBER, Workspace::ROLE_MEMBER);
    }

    private function update(): UpdateWorkspaceUseCase
    {
        return new UpdateWorkspaceUseCase($this->workspaces);
    }

    private function add(): AddWorkspaceMemberUseCase
    {
        return new AddWorkspaceMemberUseCase($this->workspaces);
    }

    private function remove(): RemoveWorkspaceMemberUseCase
    {
        return new RemoveWorkspaceMemberUseCase($this->workspaces);
    }

    // --- update -----------------------------------------------------------

    public function testAnAdminCanRenameTheWorkspace(): void
    {
        $result = $this->update()->execute(
            $this->workspaceId,
            ['name' => 'Atlas Platform'],
            self::OWNER
        );

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertSame('Atlas Platform', $this->workspaces->findById($this->workspaceId)['name']);
    }

    public function testRenamingDoesNotChangeTheSlug(): void
    {
        $before = $this->workspaces->findById($this->workspaceId)['slug'];

        $this->update()->execute($this->workspaceId, ['name' => 'Something Else'], self::OWNER);

        $this->assertSame($before, $this->workspaces->findById($this->workspaceId)['slug']);
    }

    public function testUpdateDoesNotCreateASecondWorkspace(): void
    {
        $this->update()->execute($this->workspaceId, ['name' => 'Renamed'], self::OWNER);

        $this->assertSame(1, $this->workspaces->count());
    }

    public function testAPlainMemberCannotEditTheWorkspace(): void
    {
        $result = $this->update()->execute(
            $this->workspaceId,
            ['name' => 'Hijacked'],
            self::PLAIN_MEMBER
        );

        $this->assertFalse($result['success']);
        $this->assertSame('Atlas', $this->workspaces->findById($this->workspaceId)['name']);
    }

    public function testANonMemberCannotEditTheWorkspace(): void
    {
        $result = $this->update()->execute(
            $this->workspaceId,
            ['name' => 'Hijacked'],
            self::OUTSIDER
        );

        $this->assertFalse($result['success']);
        $this->assertSame('Atlas', $this->workspaces->findById($this->workspaceId)['name']);
    }

    public function testAnEmptyNameIsRefused(): void
    {
        $result = $this->update()->execute($this->workspaceId, ['name' => '   '], self::OWNER);

        $this->assertFalse($result['success']);
        $this->assertSame('Atlas', $this->workspaces->findById($this->workspaceId)['name']);
    }

    /**
     * Database::update() builds its SET clause from the array keys, so a
     * use case that passed the caller's array through would let a request
     * body reassign the workspace.
     */
    public function testTheCallerCannotReassignOwnershipThroughTheUpdate(): void
    {
        $result = $this->update()->execute(
            $this->workspaceId,
            ['name' => 'Atlas', 'owner_id' => self::OUTSIDER, 'slug' => 'stolen'],
            self::OWNER
        );

        $workspace = $this->workspaces->findById($this->workspaceId);

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertSame(self::OWNER, $workspace['owner_id']);
        $this->assertNotSame('stolen', $workspace['slug']);
    }

    // --- addMember --------------------------------------------------------

    public function testAnAdminCanAddAMember(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: 20,
            actorId: self::OWNER
        );

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertTrue($this->workspaces->userHasAccess($this->workspaceId, 20));
    }

    public function testAPlainMemberCannotAddAnyone(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: 20,
            actorId: self::PLAIN_MEMBER
        );

        $this->assertFalse($result['success']);
        $this->assertFalse($this->workspaces->userHasAccess($this->workspaceId, 20));
    }

    public function testANonMemberCannotAddAnyoneIncludingThemselves(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: self::OUTSIDER,
            actorId: self::OUTSIDER,
            role: Workspace::ROLE_ADMIN
        );

        $this->assertFalse($result['success']);
        $this->assertFalse($this->workspaces->userHasAccess($this->workspaceId, self::OUTSIDER));
    }

    public function testAnExistingMemberIsNotAddedTwice(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: self::PLAIN_MEMBER,
            actorId: self::OWNER
        );

        $this->assertFalse($result['success']);
        $this->assertCount(3, $this->workspaces->getMembers($this->workspaceId));
    }

    public function testARoleTheColumnCannotHoldIsRefused(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: 20,
            actorId: self::OWNER,
            role: 'superadmin'
        );

        $this->assertFalse($result['success']);
        $this->assertFalse($this->workspaces->userHasAccess($this->workspaceId, 20));
    }

    /**
     * 'owner' is in the ENUM but is not assignable: ownership lives in
     * `workspaces`.`owner_id`, and writing it into a membership row would
     * give two answers that can disagree.
     */
    public function testTheOwnerRoleCannotBeHandedOutAsAMembership(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: 20,
            actorId: self::OWNER,
            role: Workspace::ROLE_OWNER
        );

        $this->assertFalse($result['success']);
        $this->assertFalse($this->workspaces->userHasAccess($this->workspaceId, 20));
    }

    public function testTheRoleIsCheckedBeforeAuthorisationIsEvenConsidered(): void
    {
        $result = $this->add()->execute(
            workspaceId: $this->workspaceId,
            userId: 20,
            actorId: self::OUTSIDER,
            role: 'superadmin'
        );

        $this->assertFalse($result['success']);
        $this->assertSame('Role invalide.', $result['message']);
    }

    // --- removeMember -----------------------------------------------------

    public function testAnAdminCanRemoveAPlainMember(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::PLAIN_MEMBER,
            actorId: self::OWNER
        );

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertFalse($this->workspaces->userHasAccess($this->workspaceId, self::PLAIN_MEMBER));
    }

    public function testAnAdminCanRemoveAnotherAdmin(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::SECOND_ADMIN,
            actorId: self::OWNER
        );

        $this->assertTrue($result['success'], (string) $result['message']);
        $this->assertFalse($this->workspaces->userHasAccess($this->workspaceId, self::SECOND_ADMIN));
    }

    /**
     * The decisive case. Without it a co-admin can remove the owner, leaving
     * a workspace whose owner_id points at a non-member: invisible on its own
     * owner's dashboard, and administrable by whoever is left.
     */
    public function testTheOwnerCannotBeRemovedEvenByAnotherAdmin(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::OWNER,
            actorId: self::SECOND_ADMIN
        );

        $this->assertFalse($result['success']);
        $this->assertTrue($this->workspaces->userHasAccess($this->workspaceId, self::OWNER));
    }

    public function testTheOwnerCannotRemoveThemselves(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::OWNER,
            actorId: self::OWNER
        );

        $this->assertFalse($result['success']);
        $this->assertTrue($this->workspaces->userHasAccess($this->workspaceId, self::OWNER));
    }

    public function testAPlainMemberCannotRemoveAnyone(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::SECOND_ADMIN,
            actorId: self::PLAIN_MEMBER
        );

        $this->assertFalse($result['success']);
        $this->assertTrue($this->workspaces->userHasAccess($this->workspaceId, self::SECOND_ADMIN));
    }

    public function testANonMemberCannotRemoveAnyone(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::PLAIN_MEMBER,
            actorId: self::OUTSIDER
        );

        $this->assertFalse($result['success']);
        $this->assertTrue($this->workspaces->userHasAccess($this->workspaceId, self::PLAIN_MEMBER));
    }

    public function testRemovingSomeoneWhoIsNotAMemberIsReported(): void
    {
        $result = $this->remove()->execute(
            workspaceId: $this->workspaceId,
            userId: self::OUTSIDER,
            actorId: self::OWNER
        );

        $this->assertFalse($result['success']);
    }

    // --- unknown workspace ------------------------------------------------

    public function testAnUnknownWorkspaceIsRejectedByEveryPath(): void
    {
        $missing = $this->workspaceId + 999;

        $this->assertFalse(
            $this->update()->execute($missing, ['name' => 'X'], self::OWNER)['success']
        );
        $this->assertFalse(
            $this->add()->execute(
                workspaceId: $missing,
                userId: 20,
                actorId: self::OWNER
            )['success']
        );
        $this->assertFalse(
            $this->remove()->execute(
                workspaceId: $missing,
                userId: self::PLAIN_MEMBER,
                actorId: self::OWNER
            )['success']
        );
    }
}
