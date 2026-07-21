<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware, Database};
use App\SellerPanel\Services\SellerDashboardService;

class DashboardController extends SellerController
{
    private function svc(): SellerDashboardService { return new SellerDashboardService(); }

    public function index(): void
    {
        Middleware::sellerAuth();
        $vid = Auth::sellerId();
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
        Middleware::sellerAuth();
        if ($this->input('period')) {
            // AJAX call from JS loadEarnings()
            $data = $this->svc()->getEarningsSummary(Auth::sellerId(), $this->input('period', 'month'));
            $this->json(['summary' => $data, 'weekly' => $this->svc()->getWeeklyRevenue(Auth::sellerId())]);
            return;
        }
        $svc = $this->svc(); $vid = Auth::sellerId();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'      => 'Earnings',
            'section'    => 'earnings',
            'weeklyData' => $svc->getWeeklyRevenue($vid),
        ]));
    }

    public function commission(): void
    {
        Middleware::sellerAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Commission Log',
            'section' => 'commission',
            'commLog' => $this->svc()->getCommissionLog(Auth::sellerId(), (int)$this->input('page', 1)),
        ]));
    }

    public function subscription(): void
    {
        Middleware::sellerAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Subscription',
            'section' => 'subscription',
        ]));
    }

    public function reviews(): void
    {
        Middleware::sellerAuth();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'       => 'Customer Reviews',
            'section'     => 'reviews',
            'reviewsData' => $this->svc()->getReviews(Auth::sellerId(), (int)$this->input('page', 1)),
        ]));
    }
}
