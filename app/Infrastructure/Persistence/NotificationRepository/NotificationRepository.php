<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\NotificationRepository;

use App\Domain\Notification\NotificationRepositoryInterface;
use App\Infrastructure\Persistence\Database;

class NotificationRepository implements NotificationRepositoryInterface
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetch("SELECT * FROM notifications WHERE id = ?", [$id]);
    }

    public function findAll(): array
    {
        return $this->db->fetchAll("SELECT * FROM notifications ORDER BY created_at DESC");
    }

    public function save(array $data): int
    {
        if (!empty($data['id'])) {
            $id = $data['id'];
            unset($data['id']);
            $this->db->update('notifications', $data, 'id = ?', [$id]);
            return $id;
        }
        return $this->db->insert('notifications', $data);
    }

    public function delete(int $id): bool
    {
        return $this->db->delete('notifications', 'id = ?', [$id]) > 0;
    }

    public function count(): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM notifications");
        return (int)($r['c'] ?? 0);
    }

    public function getByUser(int $userId, int $limit = 50): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public function markAsRead(int $id): bool
    {
        return $this->db->update('notifications', ['is_read' => 1], 'id = ?', [$id]) > 0;
    }

    public function markAllAsRead(int $userId): bool
    {
        return $this->db->update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [$userId]) >= 0;
    }

    public function getUnreadCount(int $userId): int
    {
        $r = $this->db->fetch("SELECT COUNT(*) as c FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
        return (int)($r['c'] ?? 0);
    }
}
