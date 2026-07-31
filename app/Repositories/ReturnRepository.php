<?php
namespace App\Repositories;

use App\Core\Repository;

class ReturnRepository extends Repository
{
    protected string $table = 'returns';

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    /**
     * Whether an order item has any return/replacement/cancel request
     * that isn't rejected — used by settlement eligibility to hold
     * money until the request is resolved.
     */
    public function hasOpenRequest(int $orderItemId): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT id FROM `{$this->t()}` WHERE order_item_id=? AND status NOT IN ('rejected','completed')",
            [$orderItemId]
        );
    }

    public function hasRefundedRequest(int $orderItemId): bool
    {
        return (bool) $this->db->fetchOne(
            "SELECT id FROM `{$this->t()}` WHERE order_item_id=? AND status IN ('refunded','completed')",
            [$orderItemId]
        );
    }

    public function getRefundAmount(int $orderItemId): float
    {
        return (float) ($this->db->fetchOne(
            "SELECT COALESCE(SUM(refund_amount),0) t FROM `{$this->t()}` WHERE order_item_id=? AND status IN ('refunded','completed')",
            [$orderItemId]
        )['t'] ?? 0);
    }

    public function getForCustomer(int $userId, int $page = 1): array
    {
        $sql = "SELECT r.*, oi.product_name, o.order_number
                FROM `{$this->t()}` r
                JOIN `{$this->t('order_items')}` oi ON oi.id=r.order_item_id
                JOIN `{$this->t('orders')}` o ON o.id=r.order_id
                WHERE r.user_id=? ORDER BY r.requested_at DESC";
        return $this->paginate($sql, [$userId], $page);
    }

    public function getForSeller(int $sellerId, int $page = 1, array $f = []): array
    {
        $where = 'r.seller_id=?'; $params = [$sellerId];
        if (!empty($f['status'])) { $where .= ' AND r.status=?'; $params[] = $f['status']; }

        $sql = "SELECT r.*, oi.product_name, o.order_number, u.name as customer_name
                FROM `{$this->t()}` r
                JOIN `{$this->t('order_items')}` oi ON oi.id=r.order_item_id
                JOIN `{$this->t('orders')}` o ON o.id=r.order_id
                JOIN `{$this->t('users')}` u ON u.id=r.user_id
                WHERE $where ORDER BY r.requested_at DESC";
        return $this->paginate($sql, $params, $page);
    }

    public function getQueue(int $page = 1, array $f = []): array
    {
        $where = '1=1'; $params = [];
        if (!empty($f['status'])) { $where .= ' AND r.status=?'; $params[] = $f['status']; }
        if (!empty($f['type']))   { $where .= ' AND r.type=?';   $params[] = $f['type']; }
        if (!empty($f['search'])) { $where .= ' AND (o.order_number LIKE ? OR oi.product_name LIKE ? OR u.name LIKE ?)'; $s = '%'.$f['search'].'%'; $params[] = $s; $params[] = $s; $params[] = $s; }

        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'type', 'status', 'requested_at'], 'requested_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT r.*, oi.product_name, o.order_number, u.name as customer_name, vu.name as seller_name
                FROM `{$this->t()}` r
                JOIN `{$this->t('order_items')}` oi ON oi.id=r.order_item_id
                JOIN `{$this->t('orders')}` o ON o.id=r.order_id
                JOIN `{$this->t('users')}` u ON u.id=r.user_id
                JOIN `{$this->t('users')}` vu ON vu.id=r.seller_id
                WHERE $where ORDER BY r.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $params, $page);
    }

    public function updateStatus(int $id, string $status, ?int $byId = null, float $refundAmount = 0): void
    {
        $this->db->execute(
            "UPDATE `{$this->t()}` SET status=?, refund_amount=IF(?>0,?,refund_amount), resolved_at=NOW(), resolved_by=? WHERE id=?",
            [$status, $refundAmount, $refundAmount, $byId, $id]
        );
    }

    /** Restores stock for an order item back to the product — used for cancellations and physical returns. */
    public function restockItem(int $orderItemId): void
    {
        $item = $this->db->fetchOne(
            "SELECT product_id, quantity FROM `{$this->t('order_items')}` WHERE id=?", [$orderItemId]
        );
        if (!$item) return;
        $this->db->execute(
            "UPDATE `{$this->t('products')}` SET stock = stock + ? WHERE id=?",
            [(int) $item['quantity'], (int) $item['product_id']]
        );
    }
}
