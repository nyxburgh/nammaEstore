<?php
namespace App\Repositories;

use App\Core\Repository;

class StockNotificationRepository extends Repository
{
    protected string $table = 'stock_notifications';

    /** True if this was a new subscription, false if the customer had already asked to be notified. */
    public function subscribe(int $userId, int $productId): bool
    {
        $affected = $this->db->execute(
            "INSERT IGNORE INTO `{$this->t()}` (user_id, product_id) VALUES (?, ?)",
            [$userId, $productId]
        );
        return $affected > 0;
    }

    /** Pending (not yet notified) subscribers for a product — call when its stock goes from 0 back to available. */
    public function getPendingForProduct(int $productId): array
    {
        return $this->db->fetchAll(
            "SELECT sn.*, u.email, u.name FROM `{$this->t()}` sn
             JOIN `{$this->t('users')}` u ON u.id = sn.user_id
             WHERE sn.product_id = ? AND sn.notified_at IS NULL",
            [$productId]
        );
    }

    public function markNotified(int $productId): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET notified_at = NOW() WHERE product_id = ? AND notified_at IS NULL",
            [$productId]
        );
    }
}
