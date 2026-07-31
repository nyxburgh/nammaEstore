<?php
namespace App\Admin\Services;

use App\Repositories\OrderRepository;

class OrderService
{
    private OrderRepository $orders;

    public function __construct()
    {
        $this->orders = new OrderRepository();
    }

    public function list(int $page, array $filters): array
    {
        return $this->orders->getOrders($page, $filters);
    }

    public function stats(): array
    {
        return $this->orders->getStats();
    }

    public function find(int $id): ?array
    {
        return $this->orders->getOrderDetail($id);
    }

    public function updateStatus(int $id, string $status, int $adminId, string $note = ''): void
    {
        $this->orders->updateStatus($id, $status, $adminId, 'admin', $note);
        $this->notifyCustomer($id, $status);
        if ($status === 'delivered') $this->awardLoyaltyPoints($id);
    }

    private function awardLoyaltyPoints(int $orderId): void
    {
        $order = $this->orders->findById($orderId);
        if (!$order || (int) $order['loyalty_credited'] === 1) return;

        (new \App\Core\Services\LoyaltyService())->awardForOrder(
            $orderId, (int) $order['user_id'], (float) $order['total']
        );
        $this->orders->markLoyaltyCredited($orderId);
    }

    private function notifyCustomer(int $orderId, string $status): void
    {
        $order = $this->orders->findById($orderId);
        if (!$order) return;

        $labels = [
            'processing' => 'is being processed',
            'shipped'    => 'has been shipped',
            'delivered'  => 'has been delivered',
            'cancelled'  => 'has been cancelled',
        ];
        if (!isset($labels[$status])) return;

        (new \App\Core\Services\NotificationService())->notify(
            'customer', (int) $order['user_id'], 'order_status',
            'Order update: #' . $order['order_number'],
            'Your order ' . $labels[$status] . '.',
            APP_URL . '/account/orders/' . $orderId
        );
    }

    public function monthlyRevenue(int $months = 6): array
    {
        return $this->orders->getMonthlyRevenue($months);
    }
}
