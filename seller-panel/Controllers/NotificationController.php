<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\Core\Services\NotificationService;

class NotificationController extends SellerController
{
    public function index(): void
    {
        Middleware::sellerAuth();
        $svc = new NotificationService();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'         => 'Notifications',
            'section'       => 'notifications',
            'notifications' => $svc->getForUser('seller', Auth::sellerId(), (int) $this->input('page', 1)),
        ]));
    }

    public function markRead(string $id): void
    {
        csrf_check();
        Middleware::sellerAuth();
        (new NotificationService())->markRead((int) $id, 'seller', Auth::sellerId());
        $this->json(['success' => true]);
    }
}
