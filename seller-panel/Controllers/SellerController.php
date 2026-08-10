<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Controller, Auth};
use App\Core\Services\NotificationService;
use App\SellerPanel\Services\SellerDashboardService;

abstract class SellerController extends Controller
{
    public function __construct()
    {
        $this->viewsPath = SELLER_VIEWS;
    }

    /**
     * Minimal data shared by all seller views (for sidebar counts).
     */
    protected function baseData(): array
    {
        $vid = Auth::sellerId();
        if (!$vid) return ['seller' => null, 'sellerProfile' => null, 'stats' => null,
                           'recentOrders' => [], 'topProducts' => [], 'subInfo' => [], 'weeklyData' => [],
                           'notifUnread' => 0, 'notifRecent' => []];
        $svc = new SellerDashboardService();
        $notif = new NotificationService();
        return [
            'seller'        => Auth::seller(),
            'sellerProfile' => $svc->getSellerProfile($vid),
            'stats'         => $svc->getStats($vid),
            'subInfo'       => $svc->getSubscriptionInfo($vid),
            'recentOrders'  => [],
            'topProducts'   => [],
            'weeklyData'    => [],
            'allPlans'      => $svc->getActivePlans(),
            'notifUnread'   => $notif->unreadCount('seller', $vid),
            'notifRecent'   => $notif->getForUser('seller', $vid, 1)['data'] ?? [],
        ];
    }
}
