<?php
namespace App\Frontend\Services;

use App\Core\Database;
use App\Core\Services\NotificationService;

/**
 * Sends the "order confirmed" email — order number, items, shipping
 * address and payment status. Called once the order is genuinely
 * confirmed: immediately for COD (placeOrder()), or after the gateway
 * marks payment successful for online orders (PaymentController::callback()).
 * Uses NotificationService::emailAlso(), which never throws — a failed
 * email must not break checkout or payment confirmation.
 */
class OrderEmailService
{
    public function sendConfirmation(int $orderId): void
    {
        $db = Database::getInstance();

        $order = $db->fetchOne(
            "SELECT o.*, u.email AS user_email, u.name AS user_name
             FROM `".DB_PREFIX."orders` o
             JOIN `".DB_PREFIX."users` u ON u.id = o.user_id
             WHERE o.id = ?",
            [$orderId]
        );
        if (!$order || empty($order['user_email'])) return;

        $items = $db->fetchAll(
            "SELECT product_name, variant_label, quantity, unit_price, subtotal
             FROM `".DB_PREFIX."order_items` WHERE order_id = ?",
            [$orderId]
        );

        $siteName = SettingsService::get('site_name', 'Namma E Store');
        $subject  = "Order Confirmed — #{$order['order_number']} — {$siteName}";
        $html     = $this->buildHtml($order, $items, $siteName);

        (new NotificationService())->emailAlso($order['user_email'], $subject, $html);
    }

    private function buildHtml(array $order, array $items, string $siteName): string
    {
        $rows = '';
        foreach ($items as $item) {
            $label = e($item['product_name']) . ($item['variant_label'] ? ' (' . e($item['variant_label']) . ')' : '');
            $rows .= '<tr>'
                . '<td style="padding:8px 0;border-bottom:1px solid #f0e6ef;">' . $label . '</td>'
                . '<td style="padding:8px 0;border-bottom:1px solid #f0e6ef;text-align:center;">' . (int) $item['quantity'] . '</td>'
                . '<td style="padding:8px 0;border-bottom:1px solid #f0e6ef;text-align:right;">' . currency((float) $item['subtotal']) . '</td>'
                . '</tr>';
        }

        $paymentLine = $order['payment_method'] === 'cod'
            ? 'Cash on Delivery'
            : 'Paid online' . (!empty($order['preferred_method']) ? ' via ' . strtoupper($order['preferred_method']) : '');

        return '
        <div style="font-family:Arial,sans-serif;max-width:560px;margin:0 auto;color:#1a1a2e;">
          <h2 style="color:#c2185b;margin-bottom:4px;">Thank you for your order!</h2>
          <p style="color:#6b7280;margin-top:0;">Hi ' . e($order['user_name']) . ', your order has been placed successfully.</p>
          <table style="width:100%;border-collapse:collapse;margin:20px 0;">
            <tr><td style="padding:4px 0;color:#6b7280;">Order Number</td><td style="padding:4px 0;text-align:right;font-weight:700;">' . e($order['order_number']) . '</td></tr>
            <tr><td style="padding:4px 0;color:#6b7280;">Order Date</td><td style="padding:4px 0;text-align:right;">' . formatDateTime($order['placed_at']) . '</td></tr>
            <tr><td style="padding:4px 0;color:#6b7280;">Payment Method</td><td style="padding:4px 0;text-align:right;">' . e($paymentLine) . '</td></tr>
          </table>
          <h3 style="margin-bottom:8px;">Items</h3>
          <table style="width:100%;border-collapse:collapse;">
            <thead><tr><th style="text-align:left;color:#6b7280;font-size:.85em;padding-bottom:6px;">Item</th><th style="text-align:center;color:#6b7280;font-size:.85em;padding-bottom:6px;">Qty</th><th style="text-align:right;color:#6b7280;font-size:.85em;padding-bottom:6px;">Amount</th></tr></thead>
            <tbody>' . $rows . '</tbody>
          </table>
          <table style="width:100%;border-collapse:collapse;margin-top:8px;">
            <tr><td style="padding:4px 0;color:#6b7280;">Shipping</td><td style="padding:4px 0;text-align:right;">' . ((float) $order['shipping_charge'] == 0 ? 'FREE' : currency((float) $order['shipping_charge'])) . '</td></tr>
            <tr><td style="padding:8px 0;font-weight:700;font-size:1.1em;">Total</td><td style="padding:8px 0;text-align:right;font-weight:700;font-size:1.1em;color:#c2185b;">' . currency((float) $order['total']) . '</td></tr>
          </table>
          <h3 style="margin-bottom:8px;">Shipping To</h3>
          <p style="line-height:1.6;color:#1a1a2e;">
            ' . e($order['shipping_name']) . '<br>
            ' . e($order['shipping_address']) . '<br>
            ' . e($order['shipping_city'] . ', ' . $order['shipping_state'] . ' - ' . $order['shipping_pincode']) . '<br>
            📞 ' . e($order['shipping_phone']) . '
          </p>
          <p style="margin-top:24px;color:#6b7280;font-size:.85em;">Track your order anytime from My Account → My Orders.</p>
          <p style="color:#6b7280;font-size:.85em;">— The ' . e($siteName) . ' Team</p>
        </div>';
    }
}
