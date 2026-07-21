<?php
namespace App\Admin\Services;
use App\Core\Database;
use App\Repositories\{OrderRepository, UserRepository, ProductRepository};

class DashboardService
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function getStats(): array {
        $o = (new OrderRepository())->getStats();
        $u = (new UserRepository())->getStats();
        $p = (new ProductRepository())->getStats();
        $pending_reviews = (int)($this->db->fetchOne(
            "SELECT COUNT(*) c FROM `".DB_PREFIX."reviews` WHERE is_approved=0 AND is_flagged=0"
        )['c'] ?? 0);
        return [
            'pending_reviews' => $pending_reviews,
            'total_revenue'=>(float)$o['revenue'],'revenue_today'=>(float)$o['revenue_today'],
            'total_orders'=>(int)$o['total'],'orders_today'=>(int)$o['today'],
            'orders_placed'=>(int)$o['placed'],'orders_processing'=>(int)$o['processing'],
            'orders_shipped'=>(int)$o['shipped'],'orders_delivered'=>(int)$o['delivered'],
            'orders_cancelled'=>(int)$o['cancelled'],
            'total_customers'=>(int)$u['total_customers'],'total_sellers'=>(int)$u['total_sellers'],
            'active_sellers'=>(int)$u['active_sellers'],'pending_sellers'=>(int)$u['pending_sellers'],
            'new_users_month'=>(int)$u['new_this_month'],
            'total_products'=>(int)$p['total'],'active_products'=>(int)$p['active'],'low_stock'=>(int)$p['low_stock'],
        ];
    }
    public function getRevenueChart(int $months = 6): array {
        $rows = (new OrderRepository())->getMonthlyRevenue($months);
        $labels=$revenue=$orders=[];
        foreach ($rows as $r) { $labels[]=date('M Y',strtotime($r['month'].'-01')); $revenue[]=(float)$r['revenue']; $orders[]=(int)$r['orders']; }
        return ['labels'=>$labels,'revenue'=>$revenue,'orders'=>$orders];
    }
    public function getRecentOrders(int $limit = 8): array {
        return $this->db->fetchAll("SELECT o.id,o.order_number,o.total,o.order_status,o.payment_status,o.placed_at,u.name as customer_name FROM `".DB_PREFIX."orders` o LEFT JOIN `".DB_PREFIX."users` u ON u.id=o.user_id ORDER BY o.placed_at DESC LIMIT ?", [$limit]);
    }
    public function getTopSellers(int $limit = 5): array {
        return $this->db->fetchAll("SELECT u.name,vp.shop_name,COALESCE(SUM(ovs.gross_amount),0) as total_sales,COUNT(DISTINCT ovs.order_id) as order_count FROM `".DB_PREFIX."order_seller_splits` ovs JOIN `".DB_PREFIX."users` u ON u.id=ovs.seller_id LEFT JOIN `".DB_PREFIX."seller_profiles` vp ON vp.user_id=ovs.seller_id GROUP BY ovs.seller_id ORDER BY total_sales DESC LIMIT ?", [$limit]);
    }
    public function getPendingSellers(int $limit = 5): array {
        return $this->db->fetchAll("SELECT u.id,u.name,u.email,u.created_at,vp.shop_name,vp.status FROM `".DB_PREFIX."users` u JOIN `".DB_PREFIX."seller_profiles` vp ON vp.user_id=u.id WHERE vp.status='pending' ORDER BY u.created_at DESC LIMIT ?", [$limit]);
    }
    public function getCommissionSummary(): array {
        return $this->db->fetchOne("SELECT COALESCE(SUM(commission_amount),0) as total_commission,COALESCE(SUM(seller_earning),0) as total_seller_earning,COALESCE(AVG(commission_pct),0) as avg_commission_pct,COUNT(*) as total_transactions FROM `".DB_PREFIX."commissions` WHERE MONTH(calculated_at)=MONTH(NOW()) AND YEAR(calculated_at)=YEAR(NOW())") ?? [];
    }
}
