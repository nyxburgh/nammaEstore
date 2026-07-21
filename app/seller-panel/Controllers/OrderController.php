<?php
namespace App\SellerPanel\Controllers;
use App\Core\{Auth, Middleware};
use App\SellerPanel\Services\SellerOrderService;

class OrderController extends SellerController
{
    private function svc(): SellerOrderService { return new SellerOrderService(); }

    public function index(): void
    {
        Middleware::sellerAuth();
        $f = ['q' => $this->input('q', ''), 'status' => $this->input('status', 'all')];
        $this->view('dashboard.main', array_merge($this->baseData(), [
            'title'   => 'Orders',
            'section' => 'orders',
            'orders'  => $this->svc()->getAll(Auth::sellerId(), (int)$this->input('page', 1), $f),
            'filters' => $f,
        ]));
    }

    public function show(string $id): void
    {
        Middleware::sellerAuth();
        $order = $this->svc()->findById((int)$id, Auth::sellerId());
        if (!$order) {
            $this->setFlash('error', 'Order not found.');
            $this->redirect(SELLER_URL . '/orders');
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
        Middleware::sellerAuth();
        $r = $this->svc()->updateStatus(
            (int)$id, Auth::sellerId(),
            $this->input('status', ''),
            $this->input('note', '')
        );
        $this->json($r);
    }
}
