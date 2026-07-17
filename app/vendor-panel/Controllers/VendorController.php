<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Controller, Auth};
use App\VendorPanel\Services\VendorDashboardService;

abstract class VendorController extends Controller
{
    public function __construct()
    {
        $this->viewsPath = VENDOR_VIEWS;
    }

    /**
     * Minimal data shared by all vendor views (for sidebar counts).
     */
    protected function baseData(): array
    {
        $vid = Auth::vendorId();
        if (!$vid) return ['vendor' => null, 'vendorProfile' => null, 'stats' => null,
                           'recentOrders' => [], 'topProducts' => [], 'subInfo' => [], 'weeklyData' => []];
        $svc = new VendorDashboardService();
        return [
            'vendor'        => Auth::vendor(),
            'vendorProfile' => $svc->getVendorProfile($vid),
            'stats'         => $svc->getStats($vid),
            'subInfo'       => $svc->getSubscriptionInfo($vid),
            'recentOrders'  => [],
            'topProducts'   => [],
            'weeklyData'    => [],
            'allPlans'      => $svc->getActivePlans(),
        ];
    }
}
