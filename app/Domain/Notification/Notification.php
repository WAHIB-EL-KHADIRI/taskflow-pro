<?php

declare(strict_types=1);
namespace App\Domain\Notification;

class Notification
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly int $userId = 0,
        public readonly string $type = '',
        public readonly string $title = '',
        public readonly ?string $body = null,
        public readonly ?string $link = null,
        public readonly bool $isRead = false,
        public readonly ?string $createdAt = null,
    ) {}
}