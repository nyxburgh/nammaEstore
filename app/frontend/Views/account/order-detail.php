<?php include __DIR__.'/_sidebar.php'; ?>
<link rel="stylesheet" href="<?= asset('frontend/css/account.css') ?>">
<a href="<?= APP_URL ?>/account/orders" class="back-link">← Back to Orders</a>
<div class="card">
  <div class="card-head">
    <span class="card-title">Order #<?= e($order['order_number']) ?></span>
    <span class="status-badge status-<?= e($order['order_status']) ?>"><?= ucfirst($order['order_status']) ?></span>
  </div>
  <div class="card-body">
    <div class="order-info-grid">
      <div class="info-box"><h4>📍 Delivery Address</h4><p><strong><?= e($order['shipping_name']) ?></strong><br><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city'].', '.$order['shipping_state'].' - '.$order['shipping_pincode']) ?><br>📞 <?= e($order['shipping_phone']) ?></p></div>
      <div class="info-box"><h4>💳 Payment</h4><p><strong><?= ucfirst($order['payment_method']) ?></strong><br>Status: <?= ucfirst($order['payment_status']) ?><br>Order Date: <?= formatDate($order['placed_at']) ?><br>Total: <strong class="total-amt"><?= currency($order['total']) ?></strong></p></div>
    </div>
    <?php foreach($order['items'] as $item):
      $img = !empty($item['image']) ? '<img src="'.UPLOAD_URL.'/'.$item['image'].'" alt="'.e($item['product_name'] ?? $item['name'] ?? 'Product image').'">' : '<span>🛍️</span>';
    ?>
    <div class="order-item">
      <div class="oi-img"><?= $img ?></div>
      <div class="oi-body">
        <div class="oi-name"><?= e($item['product_name']) ?></div>
        <div class="oi-qty">Qty: <?= $item['quantity'] ?> × <?= currency($item['unit_price']) ?></div>
        <?php if($order['order_status']==='delivered'): ?>
        <button type="button" class="return-toggle-btn" data-action="show-return-form" data-item-id="<?= $item['id'] ?>">↩️ Return / Replace</button>
        <form id="ret-<?= $item['id'] ?>" class="return-form" method="POST" action="<?= APP_URL ?>/account/orders/return">
          <?= csrf_field() ?>
          <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
          <input type="hidden" name="order_item_id" value="<?= $item['id'] ?>">
          <select name="type">
            <option value="return">Return for Refund</option>
            <option value="replacement">Replacement</option>
          </select>
          <select name="reason" required>
            <option value="">Select a reason...</option>
            <option value="Damaged item">Damaged item</option>
            <option value="Wrong item received">Wrong item received</option>
            <option value="Item not as described">Item not as described</option>
            <option value="Size/fit issue">Size/fit issue</option>
            <option value="Changed my mind">Changed my mind</option>
            <option value="Other">Other</option>
          </select>
          <textarea name="note" placeholder="Additional details (optional)"></textarea>
          <button type="submit" class="btn-save">Submit Request</button>
        </form>
        <?php endif; ?>
      </div>
      <div class="oi-price"><?= currency($item['unit_price']*$item['quantity']) ?></div>
    </div>
    <?php endforeach; ?>
    <div class="order-summary-wrap">
      <div class="summary-row"><span>Subtotal</span><span><?= currency($order['subtotal']) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span class="text-green"><?= $order['shipping_charge']==0?'FREE':currency($order['shipping_charge']) ?></span></div>
      <div class="summary-row total-row"><span>Total</span><span class="amount"><?= currency($order['total']) ?></span></div>
    </div>
  </div>
</div>
<?php if(!empty($order['timeline'])): ?>
<div class="card"><div class="card-head"><span class="card-title">📍 Order Timeline</span></div><div class="card-body"><div class="tl-wrap">
  <?php foreach($order['timeline'] as $tl): ?>
  <div class="tl-item"><div class="tl-dot"></div><div><div class="tl-status"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></div><?php if($tl['note']): ?><div class="tl-note"><?= e($tl['note']) ?></div><?php endif; ?><div class="tl-time"><?= formatDateTime($tl['created_at']) ?></div></div></div>
  <?php endforeach; ?>
</div></div></div>
<?php endif; ?>

<?php if(!empty($order['invoices'])): ?>
<div class="card"><div class="card-head"><span class="card-title">🧾 Tax Invoices</span></div><div class="card-body">
  <?php foreach($order['invoices'] as $inv): ?>
  <div class="invoice-row">
    <div>
      <div class="invoice-number"><?= e($inv['invoice_number']) ?></div>
      <div class="invoice-meta">Sold by <?= e($inv['shop_name'] ?? $inv['vendor_name']) ?> · <?= currency($inv['grand_total']) ?></div>
    </div>
    <div class="invoice-actions">
      <a href="<?= APP_URL ?>/invoices/<?= $inv['id'] ?>/download" class="btn-save">⬇ Download</a>
      <button type="button" class="btn-save" data-action="email-invoice" data-invoice-id="<?= $inv['id'] ?>">✉ Email Me</button>
    </div>
  </div>
  <?php endforeach; ?>
</div></div>
<?php endif; ?>
    </div></div></div>
<?php $scripts = '<script src="'.asset('frontend/js/account.js').'"></script>'; ?>
