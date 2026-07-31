<?php
namespace App\Admin\Controllers;

use App\Core\{Auth, Middleware};
use App\Admin\Services\{OrderService, ActivityService};

class OrderController extends AdminController
{
    private OrderService $orders;

    public function __construct()
    {
        parent::__construct();
        $this->orders = new OrderService();
    }

    public function index(): void
    {
        Middleware::adminAuth();
        $f = [
            'search'         => $this->input('search'),
            'status'         => $this->input('status'),
            'payment_status' => $this->input('payment_status'),
            'date_from'      => $this->input('date_from'),
            'date_to'        => $this->input('date_to'),
            'sort'           => $this->input('sort'),
            'dir'            => $this->input('dir'),
        ];
        $this->view('orders.index', [
            'title'   => 'Orders',
            'orders'  => $this->orders->list((int) $this->input('page', 1), $f),
            'stats'   => $this->orders->stats(),
            'filters' => $f,
        ]);
    }

    public function show(string $id): void
    {
        Middleware::adminAuth();
        $order = $this->orders->find((int) $id);
        if (!$order) {
            $this->redirect(ADMIN_URL . '/orders');
            return;
        }
        $this->view('orders.show', [
            'title' => 'Order #' . e($order['order_number']),
            'order' => $order,
        ]);
    }

    public function updateStatus(string $id): void
    {
        csrf_check();
        Middleware::adminAuth();

        $status = $this->input('status');
        $note   = $this->input('note', '');

        $this->orders->updateStatus((int) $id, $status, Auth::adminId(), $note);
        (new ActivityService())->log('admin', Auth::adminId(), 'update_order_status', 'orders', "Order #$id → $status");

        $this->setFlash('success', 'Order status updated.');
        $this->back();
    }
}
