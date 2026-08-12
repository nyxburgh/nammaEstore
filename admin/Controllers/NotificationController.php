<?php
namespace App\Admin\Controllers;
use App\Core\Middleware;
use App\Repositories\SidebarRepository;

class NotificationController extends AdminController
{
    public function summary(): void
    {
        Middleware::adminAuth();
        $c = (new SidebarRepository())->getAdminCounts();

        $items = [];
        if ($c['pending_sellers'] > 0) {
            $items[] = ['icon' => '🏪', 'text' => $c['pending_sellers'] . ' seller' . ($c['pending_sellers'] > 1 ? 's' : '') . ' awaiting approval', 'link' => ADMIN_URL . '/sellers?status=pending'];
        }
        if ($c['pending_reviews'] > 0) {
            $items[] = ['icon' => '⭐', 'text' => $c['pending_reviews'] . ' review' . ($c['pending_reviews'] > 1 ? 's' : '') . ' awaiting moderation', 'link' => ADMIN_URL . '/reviews?status=pending'];
        }
        if ($c['pending_returns'] > 0) {
            $items[] = ['icon' => '↩️', 'text' => $c['pending_returns'] . ' return request' . ($c['pending_returns'] > 1 ? 's' : '') . ' to review', 'link' => ADMIN_URL . '/returns'];
        }
        if ($c['pending_withdrawals'] > 0) {
            $items[] = ['icon' => '💸', 'text' => $c['pending_withdrawals'] . ' withdrawal request' . ($c['pending_withdrawals'] > 1 ? 's' : '') . ' pending', 'link' => ADMIN_URL . '/settlements/withdrawals'];
        }

        $this->json([
            'success' => true,
            'total'   => array_sum($c),
            'items'   => $items,
        ]);
    }
}
