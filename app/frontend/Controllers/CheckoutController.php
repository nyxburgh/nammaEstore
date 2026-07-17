<?php
namespace App\Frontend\Controllers;
use App\Core\{Auth, Middleware};
use App\Core\Services\LoyaltyService;
use App\Frontend\Services\{CartService, CheckoutService, AccountService, SettingsService, CouponValidationService, GiftCardService, CustomerWalletService};

class CheckoutController extends FrontendController
{
    public function index(): void {
        Middleware::userAuth();
        $cart = new CartService();
        $summary = $cart->getSummary();
        if (empty($summary['items'])) { $this->redirect(APP_URL.'/cart'); return; }
        $loyalty = new LoyaltyService();
        $this->view('checkout.index', [
            'title'     => 'Checkout',
            'summary'   => $summary,
            'addresses' => (new AccountService())->getAddresses(Auth::userId()),
            'cartCount' => $cart->getCount(),
            'settings'  => SettingsService::all(),
            'categories'=> (new \App\Frontend\Services\ProductService())->getCategories(),
            'walletBalance'   => (new CustomerWalletService())->balance(Auth::userId()),
            'loyaltyEnabled'  => $loyalty->isEnabled(),
            'loyaltyBalance'  => $loyalty->balance(Auth::userId()),
            'loyaltyMinRedeem'=> $loyalty->minRedeem(),
            'loyaltyRupeeValue' => $loyalty->pointsToRupees($loyalty->balance(Auth::userId())),
        ]);
    }

    /** AJAX: preview a coupon's discount without applying it. */
    public function applyCoupon(): void {
        csrf_check();
        Middleware::userAuth();
        $summary = (new CartService())->getSummary();
        $r = (new CouponValidationService())->validate(
            $this->input('code', ''), Auth::userId(), $summary['subtotal'], $summary['items']
        );
        if (!$r['valid']) { $this->json(['success' => false, 'message' => $r['message']]); return; }
        $this->json(['success' => true, 'discount' => $r['discount'], 'code' => $r['coupon']['code']]);
    }

    /** AJAX: preview a gift card's applicable balance without redeeming it. */
    public function applyGiftCard(): void {
        csrf_check();
        Middleware::userAuth();
        $r = (new GiftCardService())->validate($this->input('code', ''));
        if (!$r['valid']) { $this->json(['success' => false, 'message' => $r['message']]); return; }
        $this->json(['success' => true, 'balance' => (float) $r['card']['current_balance']]);
    }

    public function process(): void {
        csrf_check();
        Middleware::userAuth();
        $cart    = new CartService();
        $summary = $cart->getSummary();
        if (empty($summary['items'])) { $this->setFlash('error','Cart is empty.'); $this->redirect(APP_URL.'/cart'); return; }

        $address = [
            'name'    => $this->input('shipping_name'),
            'phone'   => $this->input('shipping_phone'),
            'address' => $this->input('shipping_address'),
            'city'    => $this->input('shipping_city'),
            'state'   => $this->input('shipping_state'),
            'pincode' => $this->input('shipping_pincode'),
        ];
        foreach ($address as $k => $v) {
            if (empty($v)) { $this->setFlash('error','All address fields are required.'); $this->redirect(APP_URL.'/checkout'); return; }
        }
        $paymentMethod = $this->input('payment_method','cod');
        if (!in_array($paymentMethod, ['cod','online'], true)) $paymentMethod = 'cod';
        $result = (new CheckoutService())->placeOrder(
            $summary['items'], $address, $paymentMethod, Auth::userId(),
            $this->input('coupon_code') ?: null,
            $this->input('gift_card_code') ?: null,
            (float) $this->input('wallet_amount', 0),
            (int) $this->input('loyalty_points', 0)
        );
        if (!$result['success']) { $this->setFlash('error', $result['message']); $this->redirect(APP_URL.'/checkout'); return; }
        $cart->clear();
        // Online orders go to the payment gateway step; the order is
        // already recorded with payment_status='pending'.
        if ($paymentMethod === 'online' && (float) ($result['total'] ?? 0) > 0) {
            $this->redirect(APP_URL.'/payment/'.$result['order_id']);
            return;
        }
        $this->redirect(APP_URL.'/checkout/success/'.$result['order_id']);
    }

    public function success(string $id): void {
        Middleware::userAuth();
        $order = (new AccountService())->getOrderDetail((int)$id, Auth::userId());
        if (!$order) { $this->redirect(APP_URL.'/account/orders'); return; }
        $this->view('checkout.success', [
            'title'    => 'Order Confirmed!',
            'order'    => $order,
            'cartCount'=> (new CartService())->getCount(),
            'settings' => SettingsService::all(),
            'categories'=> (new \App\Frontend\Services\ProductService())->getCategories(),
        ]);
    }
}
