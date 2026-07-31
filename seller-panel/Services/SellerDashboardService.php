<?php
namespace App\SellerPanel\Services;
use App\Core\Database;
use App\Admin\Services\CommissionService;

class SellerDashboardService
{
    private Database $db;

    public function __construct() { $this->db = Database::getInstance(); }

    public function getActivePlans(): array {
        return $this->db->fetchAll(
            "SELECT * FROM `".DB_PREFIX."subscription_plans` WHERE is_active=1 ORDER BY price ASC"
        );
    }

    public function getSellerProfile(int $sellerId): ?array {
        return $this->db->fetchOne(
            "SELECT vp.*, sp.name as plan_name
             FROM `".DB_PREFIX."seller_profiles` vp
             LEFT JOIN `".DB_PREFIX."seller_subscriptions` vs
                   ON vs.seller_id = vp.user_id AND vs.status = 'active'
             LEFT JOIN `".DB_PREFIX."subscription_plans` sp ON sp.id = vs.plan_id
             WHERE vp.user_id = ?", [$sellerId]
        );
    }

    public function getStats(int $sellerId): array {
        $revenue = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(gross_amount),0) s FROM `".DB_PREFIX."order_seller_splits` WHERE seller_id=?", [$sellerId])['s'] ?? 0);
        $earning = (float)($this->db->fetchOne(
            "SELECT COALESCE(SUM(seller_earning),0) s FROM `".DB_PREFIX."order_seller_splits` WHERE seller_id=?", [$sellerId])['s'] ?? 0);
        $orders  = (int)($this->db->fetchOne(
            "SELECT COUNT(DISTINCT order_id) c FROM `".DB_PREFIX."order_items` WHERE seller_id=?", [$sellerId])['c'] ?? 0);
        $products= (int)($this->db->fetchOne(
            "SELECT COUNT(*) c FROM `".DB_PREFIX."products` WHERE seller_id=? AND status='active'", [$sellerId])['c'] ?? 0);
        $rating  = (float)($this->db->fetchOne(
            "SELECT COALESCE(AVG(r.rating),0) avg FROM `".DB_PREFIX."reviews` r JOIN `".DB_PREFIX."products` p ON p.id=r.product_id WHERE p.seller_id=? AND r.is_approved=1", [$sellerId])['avg'] ?? 0);
        $pending = (int)($this->db->fetchOne(
            "SELECT COUNT(DISTINCT oi.order_id) c FROM `".DB_PREFIX."order_items` oi JOIN `".DB_PREFIX."orders` o ON o.id=oi.order_id WHERE oi.seller_id=? AND o.order_status IN ('placed','processing')", [$sellerId])['c'] ?? 0);
        return compact('revenue','earning','orders','products','rating','pending');
    }

    public function getRecentOrders(int $sellerId, int $limit = 10): array {
        return $this->db->fetchAll(
            "SELECT o.*, oi.product_name, oi.quantity, oi.unit_price, oi.subtotal as item_sub,
                    oi.id as item_id, u.name as customer_name,
                    ovs.commission_pct, ovs.commission_amount, ovs.seller_earning
             FROM `".DB_PREFIX."orders` o
             JOIN `".DB_PREFIX."order_items` oi ON oi.order_id=o.id
             JOIN `".DB_PREFIX."users` u ON u.id=o.user_id
             LEFT JOIN `".DB_PREFIX."order_seller_splits` ovs ON ovs.order_id=o.id AND ovs.seller_id=?
             WHERE oi.seller_id=?
             ORDER BY o.placed_at DESC LIMIT ?",
            [$sellerId, $sellerId, $limit]
        );
    }

    public function getTopProducts(int $sellerId, int $limit = 5): array {
        return $this->db->fetchAll(
            "SELECT p.*, c.name as cat_name,
                    COALESCE(SUM(oi.quantity),0) as total_sold,
                    COALESCE(SUM(oi.subtotal),0) as total_revenue,
                    (SELECT image_path FROM `".DB_PREFIX."product_images` WHERE product_id=p.id AND is_primary=1 LIMIT 1) as image
             FROM `".DB_PREFIX."products` p
             LEFT JOIN `".DB_PREFIX."order_items` oi ON oi.product_id=p.id
             LEFT JOIN `".DB_PREFIX."categories` c ON c.id=p.category_id
             WHERE p.seller_id=?
             GROUP BY p.id ORDER BY total_revenue DESC LIMIT ?",
            [$sellerId, $limit]
        );
    }

    public function getSubscriptionInfo(int $sellerId): array {
        $commSvc  = new CommissionService();
        $plan     = $commSvc->getActivePlan($sellerId);
        $turnover = $commSvc->getMonthlyTurnover($sellerId);
        $threshold= (float)($plan['free_threshold'] ?? 0);
        $pct      = $threshold > 0 ? min(100, round($turnover / $threshold * 100)) : 100;
        return ['plan' => $plan, 'turnover' => $turnover, 'threshold' => $threshold, 'used_pct' => $pct];
    }

    public function getWeeklyRevenue(int $sellerId): array {
        $rows = $this->db->fetchAll(
            "SELECT DATE(o.placed_at) as day, COALESCE(SUM(oi.subtotal),0) as revenue, COUNT(DISTINCT o.id) as orders
             FROM `".DB_PREFIX."orders` o
             JOIN `".DB_PREFIX."order_items` oi ON oi.order_id=o.id AND oi.seller_id=?
             WHERE o.placed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
             GROUP BY DATE(o.placed_at) ORDER BY day ASC",
            [$sellerId]
        );
        $result = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $found = array_filter($rows, fn($r) => $r['day'] === $d);
            $found = $found ? array_values($found)[0] : ['revenue' => 0, 'orders' => 0];
            $result[] = ['day' => date('D', strtotime($d)), 'revenue' => $found['revenue'], 'orders' => $found['orders']];
        }
        return $result;
    }

    public function getEarningsSummary(int $sellerId, string $period = 'month'): array {
        $where = match($period) {
            'today'  => "DATE(o.placed_at) = CURDATE()",
            'week'   => "o.placed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            'month'  => "MONTH(o.placed_at)=MONTH(NOW()) AND YEAR(o.placed_at)=YEAR(NOW())",
            default  => "YEAR(o.placed_at)=YEAR(NOW())",
        };
        $row = $this->db->fetchOne(
            "SELECT COALESCE(SUM(ovs.gross_amount),0) gross, COALESCE(SUM(ovs.commission_amount),0) comm, COALESCE(SUM(ovs.seller_earning),0) net, COUNT(DISTINCT ovs.order_id) orders
             FROM `".DB_PREFIX."order_seller_splits` ovs JOIN `".DB_PREFIX."orders` o ON o.id=ovs.order_id
             WHERE ovs.seller_id=? AND $where", [$sellerId]
        );
        return $row ?? ['gross' => 0, 'comm' => 0, 'net' => 0, 'orders' => 0];
    }

    public function getCommissionLog(int $sellerId, int $page = 1): array {
        $sql = "SELECT c.*, o.order_number, p.name as product_name FROM `".DB_PREFIX."commissions` c
                JOIN `".DB_PREFIX."orders` o ON o.id=c.order_id
                LEFT JOIN `".DB_PREFIX."products` p ON p.id=c.product_id
                WHERE c.seller_id=? ORDER BY c.calculated_at DESC";
        return $this->db->paginate($sql, [$sellerId], $page);
    }

    public function getReviews(int $sellerId, int $page = 1): array {
        $sql = "SELECT r.*, p.name as product_name, u.name as user_name
                FROM `".DB_PREFIX."reviews` r
                JOIN `".DB_PREFIX."products` p ON p.id=r.product_id
                JOIN `".DB_PREFIX."users` u ON u.id=r.user_id
                WHERE p.seller_id=? ORDER BY r.created_at DESC";
        return $this->db->paginate($sql, [$sellerId], $page);
    }
}
