<link rel="stylesheet" href="<?= asset('frontend/css/checkout.css') ?>">
<div class="container pay-container">
  <div class="card">
    <div class="card-head"><span class="card-title">💳 Complete Your Payment</span></div>
    <div class="card-body pay-body">
      <p class="pay-order-note">Order <strong><?= e($order['order_number']) ?></strong></p>
      <div class="pay-amount"><?= currency($order['total']) ?></div>

      <?php if ($gatewayOrder): ?>
        <p class="pay-hint">Pay securely via UPI, card or net banking. Your order ships once payment is confirmed.</p>
        <button id="payBtn" class="btn-place pay">Pay Now →</button>

        <form id="cbForm" method="POST" action="<?= APP_URL ?>/payment/<?= (int)$order['id'] ?>/callback">
          <?= csrf_field() ?>
          <input type="hidden" name="razorpay_order_id"   value="<?= e($gatewayOrder['gateway_order_id']) ?>">
          <input type="hidden" name="razorpay_payment_id" value="">
          <input type="hidden" name="razorpay_signature"  value="">
        </form>

        <script type="application/json" id="rzpConfig"><?= json_encode([
          'key'         => $gatewayOrder['key'],
          'amount'      => $gatewayOrder['amount'],
          'currency'    => $gatewayOrder['currency'],
          'name'        => $settings['site_name'] ?? 'Namma E Store',
          'description' => 'Order ' . $order['order_number'],
          'order_id'    => $gatewayOrder['gateway_order_id'],
          'prefill'     => [
            'name'    => $order['shipping_name'],
            'contact' => $order['shipping_phone'],
          ],
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
        <?php $scripts = '<script src="https://checkout.razorpay.com/v1/checkout.js"></script>'
                       . '<script src="'.asset('frontend/js/payment.js').'"></script>'; ?>
      <?php else: ?>
        <p class="pay-error">⚠️ <?= e($gatewayError ?? 'Payment gateway unavailable.') ?></p>
        <p class="pay-hint">Your order is saved. You can retry the payment, or switch to Cash on Delivery.</p>
        <a href="<?= APP_URL ?>/payment/<?= (int)$order['id'] ?>" class="btn-place retry">↻ Retry Payment</a>
        <form method="POST" action="<?= APP_URL ?>/payment/<?= (int)$order['id'] ?>/cod" class="cod-form">
          <?= csrf_field() ?>
          <button type="submit" class="btn-link">Switch to Cash on Delivery instead</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
