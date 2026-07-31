<link rel="stylesheet" href="<?= asset('frontend/css/cart.css') ?>">

<div class="page-hero"><h1>🛒 Your Cart (<?= count($summary['items']) ?> items)</h1></div>

<div class="container">
  <?php if(empty($summary['items'])): ?>
  <div class="card">
    <div class="card-body">
      <div class="empty-cart">
        <div class="ec-icon">🛒</div>
        <h3>Your cart is empty</h3>
        <p>Looks like you haven't added anything yet. Start shopping!</p>
        <a href="<?= APP_URL ?>/products" class="btn-continue">Start Shopping →</a>
      </div>
    </div>
  </div>
  <?php else: ?>
  <div class="cart-layout">
    <!-- CART ITEMS -->
    <div>
      <div class="card">
        <div class="card-head">
          <span class="card-title">🛍️ Cart Items (<?= count($summary['items']) ?>)</span>
          <a href="<?= APP_URL ?>/products" class="card-link">+ Add More</a>
        </div>
        <div class="card-body tight">
          <?php foreach($summary['items'] as $item):
            $price = (float)($item['sale_price']?:$item['price']) + (float)($item['price_modifier']??0);
            $orig  = (float)$item['price'] + (float)($item['price_modifier']??0);
            $imgHtml = !empty($item['image']) ? '<img src="'.UPLOAD_URL.'/'.$item['image'].'" alt="'.e($item['product_name'] ?? $item['name'] ?? 'Product image').'">' : '<span>🛍️</span>';
          ?>
          <div class="cart-item">
            <div class="ci-img"><?= $imgHtml ?></div>
            <div>
              <div class="ci-seller">🏪 <?= e($item['shop_name']??$item['seller_name']??'Shop') ?></div>
              <div class="ci-name"><?= e($item['name']) ?></div>
              <?php if(!empty($item['variant_value'])): ?><div class="ci-variant"><?= e($item['variant_name'].': '.$item['variant_value']) ?></div><?php endif; ?>
              <div class="ci-controls">
                <button class="qty-btn" data-action="cart-qty" data-item-id="<?= $item['id'] ?>" data-delta="-1">−</button>
                <span class="qty-val" id="qty-<?= $item['id'] ?>"><?= $item['quantity'] ?></span>
                <button class="qty-btn" data-action="cart-qty" data-item-id="<?= $item['id'] ?>" data-delta="1">+</button>
                <button class="btn-rm" data-action="cart-remove" data-item-id="<?= $item['id'] ?>">🗑️ Remove</button>
              </div>
            </div>
            <div>
              <div class="ci-price"><?= currency($price * $item['quantity']) ?></div>
              <?php if($orig > $price): ?><div class="ci-orig"><?= currency($orig * $item['quantity']) ?></div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- SUMMARY -->
    <div>
      <?php if($summary['shipping'] == 0): ?>
      <div class="free-ship-banner">🚚 You qualify for FREE delivery!</div>
      <?php else: $remaining = 499 - $summary['subtotal']; if($remaining > 0): ?>
      <div class="ship-nudge">Add <?= currency($remaining) ?> more for FREE delivery!</div>
      <?php endif; endif; ?>
      <div class="card">
        <div class="card-head"><span class="card-title">📋 Order Summary</span></div>
        <div class="card-body">
          <div class="summary-row"><span class="label">Taxable Value</span><span class="val"><?= currency($summary['taxable']) ?></span></div>
          <?php foreach($summary['gstBreakup'] as $gstRate => $gstAmt): ?>
          <div class="summary-row gst-row"><span class="label">GST @ <?= $gstRate ?>%</span><span class="val"><?= currency($gstAmt) ?></span></div>
          <?php endforeach; ?>
          <div class="summary-row"><span class="label">Subtotal (<?= count($summary['items']) ?> items, incl. GST)</span><span class="val"><?= currency($summary['subtotal']) ?></span></div>
          <div class="summary-row"><span class="label">Shipping</span><span class="val <?= $summary['shipping']==0?'free':'' ?>"><?= $summary['shipping']==0?'FREE':currency($summary['shipping']) ?></span></div>
          <?php if($summary['discount']>0): ?><div class="summary-row"><span class="label">Discount</span><span class="val green">-<?= currency($summary['discount']) ?></span></div><?php endif; ?>
          <div class="summary-row total"><span class="label">Total</span><span class="val grand"><?= currency($summary['total']) ?></span></div>
          <div class="coupon-form">
            <input type="text" class="coupon-input" placeholder="Coupon code">
            <button class="btn-coupon" data-action="coupon-demo">Apply</button>
          </div>
        </div>
      </div>
      <a href="<?= APP_URL ?>/checkout" class="btn-checkout">Proceed to Checkout →</a>
      <a href="<?= APP_URL ?>/products" class="btn-continue-shop">← Continue Shopping</a>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php $scripts = '<script src="'.asset('frontend/js/cart.js').'"></script>'; ?>
