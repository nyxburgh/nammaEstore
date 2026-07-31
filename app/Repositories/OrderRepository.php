<?php
namespace App\Repositories;

use App\Core\Repository;

class OrderRepository extends Repository
{
    protected string $table = 'orders';

    public function getOrders(int $page = 1, array $f = []): array
    {
        $w = '1=1'; $p = [];
        if (!empty($f['status']))         { $w .= " AND o.order_status=?";   $p[] = $f['status']; }
        if (!empty($f['payment_status'])) { $w .= " AND o.payment_status=?"; $p[] = $f['payment_status']; }
        if (!empty($f['search']))         { $w .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)"; $s='%'.$f['search'].'%'; $p[]=$s;$p[]=$s;$p[]=$s; }
        if (!empty($f['date_from']))      { $w .= " AND DATE(o.placed_at)>=?"; $p[] = $f['date_from']; }
        if (!empty($f['date_to']))        { $w .= " AND DATE(o.placed_at)<=?"; $p[] = $f['date_to']; }
        $sortCol = safeSortField($f['sort'] ?? null, ['id', 'order_number', 'total', 'placed_at'], 'placed_at');
        $sortDir = safeSortDir($f['dir'] ?? null);

        $sql = "SELECT o.*, u.name as customer_name, u.email as customer_email, COUNT(oi.id) as item_count
                FROM `{$this->t()}` o
                LEFT JOIN `{$this->t('users')}` u ON u.id=o.user_id
                LEFT JOIN `{$this->t('order_items')}` oi ON oi.order_id=o.id
                WHERE $w GROUP BY o.id ORDER BY o.{$sortCol} {$sortDir}";
        return $this->paginate($sql, $p, $page);
    }

    public function findById(int $id): ?array
    {
        return $this->db->fetchOne("SELECT * FROM `{$this->t()}` WHERE id=?", [$id]);
    }

    public function markLoyaltyCredited(int $id): void
    {
        $this->db->execute("UPDATE `{$this->t()}` SET loyalty_credited=1 WHERE id=?", [$id]);
    }

    public function getOrderDetail(int $id): ?array
    {
        $o = $this->db->fetchOne(
            "SELECT o.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
             FROM `{$this->t()}` o LEFT JOIN `{$this->t('users')}` u ON u.id=o.user_id WHERE o.id=?", [$id]);
        if (!$o) return null;
        $o['items']    = $this->db->fetchAll("SELECT oi.*, (SELECT image_path FROM `{$this->t('product_images')}` WHERE product_id=oi.product_id AND is_primary=1 LIMIT 1) as product_image, u.name as seller_name, vp.shop_name FROM `{$this->t('order_items')}` oi LEFT JOIN `{$this->t('users')}` u ON u.id=oi.seller_id LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=oi.seller_id WHERE oi.order_id=?", [$id]);
        $o['splits']   = $this->db->fetchAll("SELECT ovs.*, u.name as seller_name, vp.shop_name FROM `{$this->t('order_seller_splits')}` ovs LEFT JOIN `{$this->t('users')}` u ON u.id=ovs.seller_id LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=ovs.seller_id WHERE ovs.order_id=?", [$id]);
        $o['timeline'] = $this->db->fetchAll("SELECT * FROM `{$this->t('order_status_timeline')}` WHERE order_id=? ORDER BY created_at ASC", [$id]);
        $o['payment']  = $this->db->fetchOne("SELECT * FROM `{$this->t('payments')}` WHERE order_id=?", [$id]);
        return $o;
    }

    public function getStats(): array
    {
        return [
            'total'         => $this->count(),
            'today'         => $this->count("DATE(placed_at)=CURDATE()"),
            'placed'        => $this->count("order_status='placed'"),
            'processing'    => $this->count("order_status='processing'"),
            'shipped'       => $this->count("order_status='shipped'"),
            'delivered'     => $this->count("order_status='delivered'"),
            'cancelled'     => $this->count("order_status='cancelled'"),
            'revenue'       => $this->db->fetchOne("SELECT COALESCE(SUM(total),0) as r FROM `{$this->t()}` WHERE payment_status='paid'")['r'] ?? 0,
            'revenue_today' => $this->db->fetchOne("SELECT COALESCE(SUM(total),0) as r FROM `{$this->t()}` WHERE payment_status='paid' AND DATE(placed_at)=CURDATE()")['r'] ?? 0,
        ];
    }

    public function updateStatus(int $id, string $status, int $byId, string $byType = 'admin', string $note = ''): void
    {
        $this->db->beginTransaction();
        try {
            // delivered_at anchors the return-window calculation in
            // SettlementService — without it, no order can ever become
            // settlement-eligible. Set it exactly once, the first time
            // status becomes 'delivered' (won't overwrite on re-saves).
            $this->db->execute(
                "UPDATE `{$this->t()}` SET order_status=?, delivered_at=IF(?='delivered' AND delivered_at IS NULL, NOW(), delivered_at) WHERE id=?",
                [$status, $status, $id]
            );
            $this->db->insert("INSERT INTO `{$this->t('order_status_timeline')}` (order_id,status,note,changed_by_type,changed_by_id) VALUES(?,?,?,?,?)", [$id, $status, $note, $byType, $byId]);
            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getMonthlyRevenue(int $months = 6): array
    {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(placed_at,'%Y-%m') as month, COALESCE(SUM(total),0) as revenue, COUNT(*) as orders
             FROM `{$this->t()}` WHERE payment_status='paid' AND placed_at>=DATE_SUB(NOW(),INTERVAL ? MONTH)
             GROUP BY DATE_FORMAT(placed_at,'%Y-%m') ORDER BY month ASC", [$months]);
    }

    public function findByOrderNumber(string $orderNumber): ?array
    {
        $o = $this->db->fetchOne(
            "SELECT o.*, u.name as customer_name FROM `{$this->t()}` o
             LEFT JOIN `{$this->t('users')}` u ON u.id=o.user_id WHERE o.order_number=?",
            [$orderNumber]
        );
        if (!$o) return null;
        $o['items'] = $this->db->fetchAll(
            "SELECT oi.*, (SELECT image_path FROM `{$this->t('product_images')}` WHERE product_id=oi.product_id AND is_primary=1 LIMIT 1) as image
             FROM `{$this->t('order_items')}` oi WHERE oi.order_id=?", [$o['id']]
        );
        $o['timeline'] = $this->db->fetchAll(
            "SELECT * FROM `{$this->t('order_status_timeline')}` WHERE order_id=? ORDER BY created_at ASC", [$o['id']]
        );
        $o['shipments'] = $this->db->fetchAll(
            "SELECT sh.*, u.name as seller_name, vp.shop_name FROM `{$this->t('shipments')}` sh
             JOIN `{$this->t('users')}` u ON u.id=sh.seller_id
             LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=sh.seller_id
             WHERE sh.order_id=?", [$o['id']]
        );
        return $o;
    }

    // Used by seller-panel: orders containing at least one item from this seller
    public function getOrdersForSeller(int $sellerId, int $page = 1, array $f = []): array
    {
        $w = 'oi.seller_id=?'; $p = [$sellerId];
        if (!empty($f['status'])) { $w .= " AND o.order_status=?"; $p[] = $f['status']; }
        if (!empty($f['search'])) { $w .= " AND o.order_number LIKE ?"; $p[] = '%'.$f['search'].'%'; }
        $sql = "SELECT DISTINCT o.*, u.name as customer_name
                FROM `{$this->t()}` o
                JOIN `{$this->t('order_items')}` oi ON oi.order_id=o.id
                LEFT JOIN `{$this->t('users')}` u ON u.id=o.user_id
                WHERE $w ORDER BY o.placed_at DESC";
        return $this->paginate($sql, $p, $page);
    }
}
