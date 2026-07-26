<?php

declare(strict_types=1);
namespace App\Domain\Shared;

interface RepositoryInterface
{
    public function findById(int $id): ?array;
    public function findAll(): array;
    public function save(array $data): int;
    public function delete(int $id): bool;
    public function count(): int;
}