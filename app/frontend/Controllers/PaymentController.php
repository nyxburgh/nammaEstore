<?php
namespace App\Frontend\Controllers;
use App\Core\{Auth, Middleware, Database, ProviderFactory};
use App\Frontend\Services\{CartService, SettingsService};

/**
 * Online-payment step after checkout. The order already exists with
 * payment_status='pending'; this controller creates the gateway order,
 * renders the Razorpay checkout page, and verifies the signed callback.
 */
class PaymentController extends FrontendController
{
    private function db(): Database { return Database::getInstance(); }

    /** Loads an order only if it belongs to the user and still awaits online payment. */
    private function payableOrder(int $orderId): ?array
    {
        return $this->db()->fetchOne(
            "SELECT * FROM `".DB_PREFIX."orders`
             WHERE id=? AND user_id=? AND payment_method='online' AND payment_status='pending'",
            [$orderId, Auth::userId()]
        );
    }

    public function page(string $id): void
    {
        Middleware::userAuth();
        $order = $this->payableOrder((int) $id);
        if (!$order) { $this->redirect(APP_URL.'/account/orders'); return; }

        $gatewayOrder = null;
        $gatewayError = null;
        try {
            $g = ProviderFactory::payment()->createOrder(
                (float) $order['total'], 'INR',
                ['receipt' => $order['order_number'], 'order_id' => (string) $order['id']]
            );
            if (!empty($g['gateway_order_id'])) {
                $gatewayOrder = $g;
                $this->db()->execute(
                    "UPDATE `".DB_PREFIX."payments`
                     SET gateway='razorpay', gateway_response=? WHERE order_id=? AND status='pending'",
                    [json_encode($g['raw'] ?? []), $order['id']]
                );
            } else {
                $gatewayError = $g['raw']['error']['description']
                    ?? 'Payment gateway is not available right now.';
            }
        } catch (\Exception $e) {
            error_log('Gateway order creation failed for order '.$order['id'].': '.$e->getMessage());
            $gatewayError = 'Payment gateway is not available right now.';
        }

        $this->view('checkout.payment', [
            'title'        => 'Complete Payment',
            'order'        => $order,
            'gatewayOrder' => $gatewayOrder,
            'gatewayError' => $gatewayError,
            'cartCount'    => (new CartService())->getCount(),
            'settings'     => SettingsService::all(),
            'categories'   => (new \App\Frontend\Services\ProductService())->getCategories(),
        ]);
    }

    public function callback(string $id): void
    {
        csrf_check();
        Middleware::userAuth();
        $order = $this->payableOrder((int) $id);
        if (!$order) { $this->redirect(APP_URL.'/account/orders'); return; }

        $payload = [
            'razorpay_order_id'   => $this->input('razorpay_order_id', ''),
            'razorpay_payment_id' => $this->input('razorpay_payment_id', ''),
            'razorpay_signature'  => $this->input('razorpay_signature', ''),
        ];
        if (!ProviderFactory::payment()->verifySignature($payload)) {
            $this->setFlash('error', 'Payment verification failed. If you were charged, contact support with your payment ID.');
            $this->redirect(APP_URL.'/payment/'.$order['id']);
            return;
        }

        $this->db()->execute(
            "UPDATE `".DB_PREFIX."payments`
             SET status='success', transaction_id=?, gateway='razorpay', paid_at=NOW(), gateway_response=?
             WHERE order_id=? AND status='pending'",
            [$payload['razorpay_payment_id'], json_encode($payload), $order['id']]
        );
        $this->db()->execute(
            "UPDATE `".DB_PREFIX."orders` SET payment_status='paid' WHERE id=?",
            [$order['id']]
        );
        $this->setFlash('success', 'Payment received — thank you!');
        $this->redirect(APP_URL.'/checkout/success/'.$order['id']);
    }

    /** Fallback when the gateway is unreachable: convert the order to COD. */
    public function switchToCod(string $id): void
    {
        csrf_check();
        Middleware::userAuth();
        $order = $this->payableOrder((int) $id);
        if (!$order) { $this->redirect(APP_URL.'/account/orders'); return; }

        $this->db()->execute(
            "UPDATE `".DB_PREFIX."orders` SET payment_method='cod' WHERE id=?", [$order['id']]
        );
        $this->db()->execute(
            "UPDATE `".DB_PREFIX."payments` SET status='failed', gateway_response=? WHERE order_id=? AND status='pending'",
            [json_encode(['note' => 'Customer switched to COD']), $order['id']]
        );
        $this->setFlash('success', 'Order switched to Cash on Delivery.');
        $this->redirect(APP_URL.'/checkout/success/'.$order['id']);
    }
}
