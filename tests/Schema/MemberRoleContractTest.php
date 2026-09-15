<?php

declare(strict_types=1);

namespace Tests\Schema;

use App\Domain\Project\Project;
use App\Domain\Team\Team;
use PHPUnit\Framework\TestCase;

/**
 * The application layer decides who may do what by comparing a role string
 * against a literal. The database decides which role strings can exist at
 * all. Nothing connected the two, and they drifted: the code asked for
 * 'admin' in two places where the column never offered it.
 *
 * These tests read the ENUM out of database/migrate.php and compare it with
 * the constants the use cases actually branch on, so the next edit to either
 * side fails here rather than in production.
 */
final class MemberRoleContractTest extends TestCase
{
    private static function migration(): string
    {
        $sql = file_get_contents(__DIR__ . '/../../database/migrate.php');
        self::assertIsString($sql, 'database/migrate.php is unreadable');

        return $sql;
    }

    /** @return list<string> */
    private static function enumFor(string $table): array
    {
        $sql = self::migration();

        $found = preg_match(
            '/CREATE TABLE IF NOT EXISTS `' . preg_quote($table, '/') . '`(.*?)\)\s*ENGINE=/s',
            $sql,
            $table_body
        );
        self::assertSame(1, $found, "no CREATE TABLE found for `$table`");

        $found = preg_match('/`role`\s+ENUM\(([^)]*)\)/i', $table_body[1], $enum);
        self::assertSame(1, $found, "`$table` has no `role` ENUM column");

        preg_match_all("/'([^']*)'/", $enum[1], $values);

        return $values[1];
    }

    public function testTeamMembersColumnDefinesExactlyTheRolesTheCodeKnows(): void
    {
        $this->assertSame(Team::ALLOWED_ROLES, self::enumFor('team_members'));
    }

    public function testProjectMembersColumnDefinesExactlyTheRolesTheCodeKnows(): void
    {
        $this->assertSame(Project::ALLOWED_ROLES, self::enumFor('project_members'));
    }

    public function testThePrivilegedTeamRoleExistsInTheColumn(): void
    {
        $this->assertContains(Team::ROLE_LEAD, self::enumFor('team_members'));
    }

    public function testThePrivilegedProjectRoleExistsInTheColumn(): void
    {
        $this->assertContains(Project::ROLE_MANAGER, self::enumFor('project_members'));
    }

    /**
     * Workspaces are the reason this went unnoticed: `workspace_members`
     * really does define 'admin', so the one use case that was copied from
     * worked, and the two copies of it did not.
     */
    public function testWorkspaceMembersStillDefinesTheAdminRoleItsUseCaseWrites(): void
    {
        $this->assertContains('admin', self::enumFor('workspace_members'));
    }

    public function testNoMembershipColumnSilentlyGainedTheAdminRoleBack(): void
    {
        $this->assertNotContains('admin', self::enumFor('team_members'));
        $this->assertNotContains('admin', self::enumFor('project_members'));
    }
}
