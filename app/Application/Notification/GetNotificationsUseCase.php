<?php

declare(strict_types=1);

namespace App\Application\Notification;

use App\Domain\Notification\NotificationRepositoryInterface;

class GetNotificationsUseCase
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function execute(int $userId, int $limit = 50): array
    {
        $notifications = $this->notificationRepository->getByUser($userId, $limit);
        $unreadCount = $this->notificationRepository->getUnreadCount($userId);

        return [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ];
    }
}
