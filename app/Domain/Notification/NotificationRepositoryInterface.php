<?php
declare(strict_types=1);
namespace App\Domain\Notification;

use App\Domain\Shared\RepositoryInterface;

interface NotificationRepositoryInterface extends RepositoryInterface
{
    public function getByUser(int $userId, int $limit = 50): array;
    public function markAsRead(int $id): bool;
    public function markAllAsRead(int $userId): bool;
    public function getUnreadCount(int $userId): int;
}
