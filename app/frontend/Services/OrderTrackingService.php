<?php
namespace App\Frontend\Services;

use App\Repositories\OrderRepository;

class OrderTrackingService
{
    private OrderRepository $orders;

    public function __construct()
    {
        $this->orders = new OrderRepository();
    }

    public function track(string $orderNumber): ?array
    {
        return $this->orders->findByOrderNumber($orderNumber);
    }
}
