<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Admin\Services\DashboardService;

class DashboardController extends AdminController
{
    public function index(): void {
        Middleware::adminAuth();
        $svc = new DashboardService();
        $this->view('dashboard.index', [
            'title'          => 'Dashboard',
            'stats'          => $svc->getStats(),
            'revenueChart'   => $svc->getRevenueChart(6),
            'recentOrders'   => $svc->getRecentOrders(8),
            'topVendors'     => $svc->getTopVendors(5),
            'pendingVendors' => $svc->getPendingVendors(5),
            'commission'     => $svc->getCommissionSummary(),
        ]);
    }
}
