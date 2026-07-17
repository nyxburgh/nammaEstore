<?php
namespace App\Frontend\Controllers;
use App\Frontend\Services\{CartService, SettingsService, ProductService, OrderTrackingService};

class OrderController extends FrontendController
{
    public function trackForm(): void {
        $this->view('orders.track', [
            'title'     => 'Track Order',
            'cartCount' => (new CartService())->getCount(),
            'settings'  => SettingsService::all(),
            'categories'=> (new ProductService())->getCategories(),
        ]);
    }
    public function track(string $orderNumber): void {
        $order = (new OrderTrackingService())->track($orderNumber);
        if (!$order) { $this->setFlash('error','Order not found.'); $this->redirect(APP_URL.'/track'); return; }
        $this->view('orders.track', [
            'title'     => 'Order #'.e($orderNumber),
            'order'     => $order,
            'cartCount' => (new CartService())->getCount(),
            'settings'  => SettingsService::all(),
            'categories'=> (new ProductService())->getCategories(),
        ]);
    }
}
