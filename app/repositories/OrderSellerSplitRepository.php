<?php
namespace App\Repositories;

use App\Core\Repository;

class OrderSellerSplitRepository extends Repository
{
    protected string $table = 'order_seller_splits';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    /**
     * Splits whose order has been delivered but that haven't been
     * marked 'paid' yet — the candidate pool the settlement engine
     * evaluates on every run.
     */
    public function getCandidatesForSettlement(): array
    {
        return $this->db->fetchAll(
            "SELECT ovs.*, o.delivered_at, o.order_status, o.payment_status, vp.seller_type,
                    vp.user_id as seller_user_id
             FROM `{$this->t()}` ovs
             JOIN `{$this->t('orders')}` o ON o.id=ovs.order_id
             JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=ovs.seller_id
             WHERE o.order_status='delivered'
               AND o.payment_status='paid'
               AND ovs.payout_status='pending'"
        );
    }

    /** All order_item ids belonging to this seller within this order — used to check for open returns/disputes. */
    public function getItemIds(int $orderId, int $sellerId): array
    {
        $rows = $this->db->fetchAll(
            "SELECT id FROM `{$this->t('order_items')}` WHERE order_id=? AND seller_id=?",
            [$orderId, $sellerId]
        );
        return array_column($rows, 'id');
    }

    public function markPaid(int $id): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET payout_status='paid', payout_date=NOW() WHERE id=?",
            [$id]
        );
    }

    public function markOnHold(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET payout_status='on_hold' WHERE id=?", [$id]);
    }

    public function markProcessing(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET payout_status='processing' WHERE id=?", [$id]);
    }
}
