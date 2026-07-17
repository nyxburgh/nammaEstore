<?php
namespace App\Admin\Services;
use App\Core\Database;

class CommissionService
{
    private Database $db;
    public function __construct() { $this->db = Database::getInstance(); }

    public function calculate(int $vendorId, float $amount): array {
        $plan     = $this->getActivePlan($vendorId);
        $turnover = $this->getMonthlyTurnover($vendorId);
        $pct      = $this->resolvePct($plan, $turnover);
        $comm     = round($amount * $pct / 100, 2);
        return ['plan_id'=>$plan['plan_id'],'plan_name'=>$plan['plan_name'],'commission_pct'=>$pct,'commission_amount'=>$comm,'vendor_earning'=>$amount-$comm,'turnover_before'=>$turnover];
    }

    private function resolvePct(array $plan, float $turnover): float {
        if ((float)$plan['plan_price']==0) return 10.00;
        return $turnover<=(float)$plan['free_threshold'] ? 0.00 : (float)($plan['commission_after_threshold']??10);
    }

    public function getActivePlan(int $vendorId): array {
        $row = $this->db->fetchOne("SELECT vs.plan_id, sp.name as plan_name, sp.price as plan_price, sp.free_threshold, sp.commission_after_threshold FROM `".DB_PREFIX."vendor_subscriptions` vs JOIN `".DB_PREFIX."subscription_plans` sp ON sp.id=vs.plan_id WHERE vs.vendor_id=? AND vs.status='active' ORDER BY vs.started_at DESC LIMIT 1", [$vendorId]);
        if ($row) return $row;
        $free = $this->db->fetchOne("SELECT id as plan_id, name as plan_name, price as plan_price, free_threshold, commission_after_threshold FROM `".DB_PREFIX."subscription_plans` WHERE slug='free' LIMIT 1");
        return $free ?? ['plan_id'=>null,'plan_name'=>'Free Plan','plan_price'=>0,'free_threshold'=>0,'commission_after_threshold'=>10];
    }

    public function getMonthlyTurnover(int $vendorId): float {
        return (float)($this->db->fetchOne("SELECT COALESCE(total_turnover,0) as t FROM `".DB_PREFIX."vendor_monthly_turnover` WHERE vendor_id=? AND month=MONTH(NOW()) AND year=YEAR(NOW())", [$vendorId])['t']??0);
    }

    public function addToTurnover(int $vendorId, float $amount): void {
        $this->db->execute("INSERT INTO `".DB_PREFIX."vendor_monthly_turnover` (vendor_id,month,year,total_turnover,total_orders) VALUES(?,MONTH(NOW()),YEAR(NOW()),?,1) ON DUPLICATE KEY UPDATE total_turnover=total_turnover+VALUES(total_turnover),total_orders=total_orders+1", [$vendorId,$amount]);
    }
}
