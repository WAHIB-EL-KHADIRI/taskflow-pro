<?php

declare(strict_types=1);

namespace App\Application\Notification;

use App\Domain\Notification\NotificationRepositoryInterface;

class MarkNotificationsReadUseCase
{
    private NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function execute(int $userId): array
    {
        $this->notificationRepository->markAllAsRead($userId);

        return ['success' => true, 'message' => 'Notifications marquées comme lues.'];
    }
}
