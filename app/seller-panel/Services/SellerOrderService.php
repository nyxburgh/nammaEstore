<?php
namespace App\SellerPanel\Services;
use App\Core\Database;

class SellerOrderService
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getAll(int $sellerId, int $page = 1, array $f = []): array
    {
        $where  = "oi.seller_id = ?";
        $params = [$sellerId, $sellerId, $sellerId];   // JOIN, LEFT JOIN and WHERE each bind seller_id

        $extra = '';
        if (!empty($f['status']) && $f['status'] !== 'all') {
            $extra  .= " AND o.order_status = ?";
            $params[] = $f['status'];
        }
        if (!empty($f['q'])) {
            $s      = '%' . $f['q'] . '%';
            $extra  .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR oi.product_name LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s;
        }

        $sql = "SELECT o.id, o.order_number, o.order_status, o.payment_status,
                       o.placed_at, o.total,
                       u.name  AS customer_name,
                       u.phone AS customer_phone,
                       oi.product_name, oi.quantity, oi.unit_price,
                       oi.subtotal AS item_sub, oi.id AS item_id,
                       ovs.seller_earning, ovs.commission_pct
                FROM `".DB_PREFIX."orders` o
                JOIN `".DB_PREFIX."order_items` oi
                     ON oi.order_id = o.id AND oi.seller_id = ?
                JOIN `".DB_PREFIX."users` u ON u.id = o.user_id
                LEFT JOIN `".DB_PREFIX."order_seller_splits` ovs
                     ON ovs.order_id = o.id AND ovs.seller_id = ?
                WHERE {$where}{$extra}
                ORDER BY o.placed_at DESC";

        return $this->db->paginate($sql, $params, $page);
    }

    public function findById(int $orderId, int $sellerId): ?array
    {
        $o = $this->db->fetchOne(
            "SELECT o.*, u.name AS customer_name, u.phone AS customer_phone,
                    u.email AS customer_email
             FROM `".DB_PREFIX."orders` o
             JOIN `".DB_PREFIX."users` u ON u.id = o.user_id
             WHERE o.id = ?",
            [$orderId]
        );
        if (!$o) return null;

        $o['items'] = $this->db->fetchAll(
            "SELECT oi.*,
                    (SELECT image_path FROM `".DB_PREFIX."product_images`
                     WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) AS image
             FROM `".DB_PREFIX."order_items` oi
             WHERE oi.order_id = ? AND oi.seller_id = ?",
            [$orderId, $sellerId]
        );
        if (empty($o['items'])) return null;

        $o['timeline'] = $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."order_status_timeline`
             WHERE order_id = ? ORDER BY created_at ASC",
            [$orderId]
        );
        $o['split'] = $this->db->fetchOne(
            "SELECT * FROM `".DB_PREFIX."order_seller_splits`
             WHERE order_id = ? AND seller_id = ?",
            [$orderId, $sellerId]
        );
        $o['shipment'] = $o['split'] ? $this->db->fetchOne(
            "SELECT * FROM `".DB_PREFIX."shipments` WHERE order_seller_split_id = ?",
            [$o['split']['id']]
        ) : null;
        return $o;
    }

    public function updateStatus(int $orderId, int $sellerId, string $status, string $note = ''): array
    {
        $allowed = ['processing', 'shipped', 'delivered', 'cancelled'];
        if (!in_array($status, $allowed))
            return ['success' => false, 'message' => 'Invalid status.'];

        // Verify seller owns items in this order
        $check = $this->db->fetchOne(
            "SELECT COUNT(*) AS c FROM `".DB_PREFIX."order_items`
             WHERE order_id = ? AND seller_id = ?",
            [$orderId, $sellerId]
        );
        if (!$check || (int)$check['c'] === 0)
            return ['success' => false, 'message' => 'Order not found.'];

        $this->db->execute(
            "UPDATE `".DB_PREFIX."orders` SET order_status=?, delivered_at=IF(?='delivered' AND delivered_at IS NULL, NOW(), delivered_at) WHERE id=?",
            [$status, $status, $orderId]
        );
        $this->db->insert(
            "INSERT INTO `".DB_PREFIX."order_status_timeline`
             (order_id, status, note, changed_by_type, changed_by_id)
             VALUES (?,?,?,'seller',?)",
            [$orderId, $status, $note, $sellerId]
        );
        $this->notifyCustomer($orderId, $status);
        if ($status === 'delivered') $this->awardLoyaltyPoints($orderId);
        return ['success' => true];
    }

    private function awardLoyaltyPoints(int $orderId): void
    {
        $order = $this->db->fetchOne("SELECT * FROM `".DB_PREFIX."orders` WHERE id=?", [$orderId]);
        if (!$order || (int) $order['loyalty_credited'] === 1) return;

        (new \App\Core\Services\LoyaltyService())->awardForOrder(
            $orderId, (int) $order['user_id'], (float) $order['total']
        );
        $this->db->execute("UPDATE `".DB_PREFIX."orders` SET loyalty_credited=1 WHERE id=?", [$orderId]);
    }

    private function notifyCustomer(int $orderId, string $status): void
    {
        $order = $this->db->fetchOne(
            "SELECT order_number, user_id FROM `".DB_PREFIX."orders` WHERE id=?", [$orderId]
        );
        if (!$order) return;

        $labels = [
            'processing' => 'is being processed',
            'shipped'    => 'has been shipped',
            'delivered'  => 'has been delivered',
            'cancelled'  => 'has been cancelled',
        ];
        if (!isset($labels[$status])) return;

        (new \App\Core\Services\NotificationService())->notify(
            'customer', (int) $order['user_id'], 'order_status',
            'Order update: #' . $order['order_number'],
            'Your order ' . $labels[$status] . '.',
            APP_URL . '/account/orders/' . $orderId
        );
    }
}
