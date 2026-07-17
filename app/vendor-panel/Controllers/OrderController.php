<?php
namespace App\VendorPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\VendorPanel\Services\VendorOrderService;

class OrderController extends VendorController
{
    private function svc(): VendorOrderService { return new VendorOrderService(); }

    public function index(): void
    {
        Middleware::vendorAuth();
        $f = ['q' => $this->input('q', ''), 'status' => $this->input('status', 'all')];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Orders',
            'section' => 'orders',
            'orders'  => $this->svc()->getAll(Auth::vendorId(), (int)$this->input('page', 1), $f),
            'filters' => $f,
        ]));
    }

    public function show(string $id): void
    {
        Middleware::vendorAuth();
        $order = $this->svc()->findById((int)$id, Auth::vendorId());
        if (!$order) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect(VENDOR_URL . '/orders');
            return;
        }
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Order #' . e($order['order_number']),
            'section' => 'order-detail',
            'order'   => $order,
        ]));
    }

    public function updateStatus(string $id): void
    {
        csrf_check();
        Middleware::vendorAuth();
        $r = $this->svc()->updateStatus(
            (int)$id, Auth::vendorId(),
            $this->input('status', ''),
            $this->input('note', '')
        );
        $this->json($r);
    }
}
