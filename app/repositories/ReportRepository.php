<?php
namespace App\Repositories;

use App\Core\Repository;

class ReportRepository extends Repository
{
    public function salesByDate(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT DATE(placed_at) as date, COUNT(*) as orders, COALESCE(SUM(total),0) as revenue,
                    SUM(CASE WHEN order_status='cancelled' THEN 1 ELSE 0 END) as cancelled
             FROM `{$this->t('orders')}`
             WHERE DATE(placed_at) BETWEEN ? AND ? AND payment_status='paid'
             GROUP BY DATE(placed_at) ORDER BY date ASC",
            [$from, $to]
        );
    }

    public function salesSummary(string $from, string $to): array
    {
        return $this->db->fetchOne(
            "SELECT COUNT(*) as total_orders, COALESCE(SUM(total),0) as total_revenue
             FROM `{$this->t('orders')}` WHERE DATE(placed_at) BETWEEN ? AND ? AND payment_status='paid'",
            [$from, $to]
        ) ?? [];
    }

    public function commissionBySeller(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT c.seller_id, u.name as seller_name, vp.shop_name, c.plan_name,
                    SUM(c.item_amount) as gross_amount, SUM(c.commission_amount) as total_commission,
                    SUM(c.seller_earning) as total_earning, COUNT(*) as transactions
             FROM `{$this->t('commissions')}` c
             JOIN `{$this->t('users')}` u ON u.id=c.seller_id
             LEFT JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=c.seller_id
             WHERE DATE(c.calculated_at) BETWEEN ? AND ?
             GROUP BY c.seller_id, c.plan_name ORDER BY total_commission DESC",
            [$from, $to]
        );
    }

    public function sellerPerformance(): array
    {
        return $this->db->fetchAll(
            "SELECT u.name, vp.shop_name, COALESCE(sp.name,'Free Plan') as plan_name,
                    COUNT(DISTINCT ovs.order_id) as total_orders,
                    COALESCE(SUM(ovs.gross_amount),0) as gross_sales,
                    COALESCE(SUM(ovs.commission_amount),0) as commission,
                    COALESCE(SUM(ovs.seller_earning),0) as earning
             FROM `{$this->t('users')}` u
             JOIN `{$this->t('seller_profiles')}` vp ON vp.user_id=u.id
             LEFT JOIN `{$this->t('seller_subscriptions')}` vs ON vs.seller_id=u.id AND vs.status='active'
             LEFT JOIN `{$this->t('subscription_plans')}` sp ON sp.id=vs.plan_id
             LEFT JOIN `{$this->t('order_seller_splits')}` ovs ON ovs.seller_id=u.id
             WHERE u.role='seller' GROUP BY u.id ORDER BY gross_sales DESC"
        );
    }
}
