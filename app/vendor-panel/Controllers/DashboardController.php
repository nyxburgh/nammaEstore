<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware, Database};
use App\VendorPanel\Services\VendorDashboardService;

class DashboardController extends VendorController
{
    private function svc(): VendorDashboardService { return new VendorDashboardService(); }

    public function index(): void
    {
        Middleware::vendorAuth();
        $vid = Auth::vendorId();
        $svc = $this->svc();
        $base = $this->baseData();
        $this->view('dashboard.main', array_merge($base, [
            'title'        => 'Dashboard',
            'section'      => 'overview',
            'recentOrders' => $svc->getRecentOrders($vid, 10),
            'topProducts'  => $svc->getTopProducts($vid, 5),
            'weeklyData'   => $svc->getWeeklyRevenue($vid),
        ]));
    }

    public function earnings(): void
    {
        Middleware::vendorAuth();
        if ($this->input('period')) {
            // AJAX call from JS loadEarnings()
            $data = $this->svc()->getEarningsSummary(Auth::vendorId(), $this->input('period', 'month'));
            $this->json(['summary' => $data, 'weekly' => $this->svc()->getWeeklyRevenue(Auth::vendorId())]);
            return;
        }
        $svc = $this->svc(); $vid = Auth::vendorId();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'      => 'Earnings',
            'section'    => 'earnings',
            'weeklyData' => $svc->getWeeklyRevenue($vid),
        ]));
    }

    public function commission(): void
    {
        Middleware::vendorAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Commission Log',
            'section' => 'commission',
            'commLog' => $this->svc()->getCommissionLog(Auth::vendorId(), (int)$this->input('page', 1)),
        ]));
    }

    public function subscription(): void
    {
        Middleware::vendorAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Subscription',
            'section' => 'subscription',
        ]));
    }

    public function reviews(): void
    {
        Middleware::vendorAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'       => 'Customer Reviews',
            'section'     => 'reviews',
            'reviewsData' => $this->svc()->getReviews(Auth::vendorId(), (int)$this->input('page', 1)),
        ]));
    }
}
