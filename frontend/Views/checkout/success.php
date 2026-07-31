<link rel="stylesheet" href="<?= asset('frontend/css/checkout.css') ?>">
<div class="container">
  <div class="success-wrap">
    <div class="success-icon">✓</div>
    <h1>Order Confirmed! 🎉</h1>
    <p>Thank you for shopping with <?= e(\App\Frontend\Services\SettingsService::get('site_name','Namma E Store')) ?>! Your order has been placed successfully.</p>
    <div class="order-num"><div class="label">Order Number</div><div class="num">#<?= e($order['order_number']) ?></div></div>
    <div class="steps-timeline">
      <?php foreach([['✅','Order Placed'],['⚙️','Processing'],['🚚','Shipped'],['🏠','Delivered']] as [$ic,$lb]): ?>
      <div class="tl-step"><div class="tl-dot"><?= $ic ?></div><div class="tl-label"><?= $lb ?></div></div>
      <?php endforeach; ?>
    </div>
    <?php if(!empty($order['items'])): ?>
    <div class="order-items">
      <div class="oh">🛍️ Items Ordered</div>
      <?php foreach($order['items'] as $item):
        $img = !empty($item['image']) ? '<img src="'.UPLOAD_URL.'/'.$item['image'].'" alt="'.e($item['product_name'] ?? $item['name'] ?? 'Product image').'">' : '<span>🛍️</span>';
      ?>
      <div class="oi">
        <div class="oi-img"><?= $img ?></div>
        <div><div class="oi-name"><?= e($item['product_name']) ?></div><div class="oi-qty">Qty: <?= $item['quantity'] ?></div></div>
        <div class="oi-price"><?= currency((float)$item['unit_price'] * (int)$item['quantity']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="success-btns">
      <a href="<?= APP_URL ?>/track/<?= urlencode($order['order_number']) ?>" class="btn-track">Track Order →</a>
      <a href="<?= APP_URL ?>/products" class="btn-shop">Continue Shopping</a>
    </div>
  </div>
</div>
