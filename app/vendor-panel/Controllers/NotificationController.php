<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\Core\Services\NotificationService;

class NotificationController extends VendorController
{
    public function index(): void
    {
        Middleware::vendorAuth();
        $svc = new NotificationService();
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'         => 'Notifications',
            'section'       => 'notifications',
            'notifications' => $svc->getForUser('vendor', Auth::vendorId(), (int) $this->input('page', 1)),
        ]));
    }

    public function markRead(string $id): void
    {
        csrf_check();
        Middleware::vendorAuth();
        (new NotificationService())->markRead((int) $id, 'vendor', Auth::vendorId());
        $this->json(['success' => true]);
    }
}
