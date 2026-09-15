<?php

declare(strict_types=1);

namespace Tests\Application\Task;

use App\Application\Task\UpdateTaskStatusUseCase;
use App\Domain\Task\TaskRepositoryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class UpdateTaskStatusUseCaseTest extends TestCase
{
    /** @return list<array{string}> */
    public static function validStatuses(): array
    {
        return [['todo'], ['in_progress'], ['review'], ['done']];
    }

    /** @return list<array{string}> */
    public static function invalidStatuses(): array
    {
        return [['archived'], ['DONE'], [''], ['done '], ['0']];
    }

    #[DataProvider('validStatuses')]
    public function testAKnownStatusIsWrittenThrough(string $status): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $repository->method('findById')->willReturn(['id' => 1]);
        $repository->expects($this->once())
            ->method('updateStatus')
            ->with(1, $status);

        $result = (new UpdateTaskStatusUseCase($repository))->execute(1, $status);

        $this->assertTrue($result['success']);
    }

    #[DataProvider('invalidStatuses')]
    public function testAnUnknownStatusNeverReachesTheRepository(string $status): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $repository->expects($this->never())->method('updateStatus');
        $repository->expects($this->never())->method('findById');

        $result = (new UpdateTaskStatusUseCase($repository))->execute(1, $status);

        $this->assertFalse($result['success']);
        $this->assertSame('Statut invalide.', $result['message']);
    }

    public function testAMissingTaskIsReportedAndNotWritten(): void
    {
        $repository = $this->createMock(TaskRepositoryInterface::class);
        $repository->method('findById')->willReturn(null);
        $repository->expects($this->never())->method('updateStatus');

        $result = (new UpdateTaskStatusUseCase($repository))->execute(404, 'done');

        $this->assertFalse($result['success']);
        $this->assertSame('Tâche introuvable.', $result['message']);
    }
}
