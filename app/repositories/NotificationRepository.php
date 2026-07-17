<?php
namespace App\Repositories;

use App\Core\Repository;

class NotificationRepository extends Repository
{
    protected string $table = 'notifications';

    public function getForUser(string $userType, int $userId, int $page = 1): array
    {
        $sql = "SELECT * FROM `{$this->t()}` WHERE user_type=? AND user_id=? ORDER BY created_at DESC";
        return $this->paginate($sql, [$userType, $userId], $page);
    }

    public function unreadCount(string $userType, int $userId): int
    {
        return $this->count("user_type=? AND user_id=? AND is_read=0", [$userType, $userId]);
    }

    public function markRead(int $id, string $userType, int $userId): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET is_read=1 WHERE id=? AND user_type=? AND user_id=?",
            [$id, $userType, $userId]
        );
    }

    public function markAllRead(string $userType, int $userId): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET is_read=1 WHERE user_type=? AND user_id=?",
            [$userType, $userId]
        );
    }
}
