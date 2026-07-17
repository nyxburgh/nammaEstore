<link rel="stylesheet" href="<?= asset('frontend/css/track.css') ?>">

<div class="page-hero">
  <h1>📦 Order Tracking</h1>
  <p>Track your order status in real-time</p>
</div>

<div class="container">
  <?php if(empty($order)): ?>
  <!-- SEARCH FORM -->
  <div class="track-form-wrap">
    <div class="track-form">
      <h3>🔍 Enter Your Order Number</h3>
      <form action="" method="GET" data-action="track-order">
        <div class="track-input-row">
          <input type="text" class="track-input" id="trackNum" placeholder="e.g. BZ250401XYZAB" value="<?= e($_GET['order']??'') ?>">
          <button type="submit" class="btn-track">Track →</button>
        </div>
      </form>
      <?php if(\App\Core\Auth::isUserLoggedIn()): ?>
      <div class="track-my-orders-link"><a href="<?= APP_URL ?>/account/orders">View all my orders →</a></div>
      <?php endif; ?>
    </div>
  </div>
  <?php else: ?>
  <!-- ORDER DETAILS -->
  <div class="order-track-wrap">
    <div class="order-summary-card">
      <div class="osc-head">
        <div><div class="osc-num">Order #<?= e($order['order_number']) ?></div><div class="osc-date">Placed on <?= formatDate($order['placed_at']) ?></div></div>
        <span class="status-badge status-<?= e($order['order_status']) ?>"><?= ucfirst($order['order_status']) ?></span>
      </div>
      <div class="osc-body">
        <?php if(!empty($order['items'])): ?>
        <div class="osc-items">
          <?php foreach($order['items'] as $item):
            $img = !empty($item['image']) ? '<img src="'.UPLOAD_URL.'/'.$item['image'].'" alt="'.e($item['product_name'] ?? $item['name'] ?? 'Product image').'">' : '<span>🛍️</span>';
          ?>
          <div class="osc-item">
            <div class="oci-img"><?= $img ?></div>
            <div><div class="oci-name"><?= e($item['product_name']) ?></div><div class="oci-qty">Qty: <?= $item['quantity'] ?></div></div>
            <div class="oci-price"><?= currency((float)$item['unit_price']*(int)$item['quantity']) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="addr-info">
          📍 <strong>Delivering to:</strong> <?= e($order['shipping_name']) ?>, <?= e($order['shipping_address'].', '.$order['shipping_city'].', '.$order['shipping_state'].' - '.$order['shipping_pincode']) ?>
          <br>📞 <?= e($order['shipping_phone']) ?>
          &nbsp;&nbsp;💰 <?= ucfirst($order['payment_method']) ?> · <?= ucfirst($order['payment_status']) ?>
        </div>
      </div>
    </div>

    <?php if(!empty($order['shipments'])): foreach($order['shipments'] as $sh): ?>
    <div class="shipment-card">
      <div class="shipment-head">
        <div><div class="shipment-courier">🚚 <?= e($sh['courier_name']) ?></div><div class="shipment-vendor">Shipped by <?= e($sh['shop_name'] ?? $sh['vendor_name']) ?></div></div>
        <span class="status-badge status-<?= $sh['status']==='delivered'?'delivered':($sh['status']==='failed'||$sh['status']==='rto'?'cancelled':'processing') ?>"><?= ucwords(str_replace('_',' ',$sh['status'])) ?></span>
      </div>
      <div class="shipment-tracking-num">Tracking #: <code><?= e($sh['tracking_number']) ?></code><?php if($sh['tracking_url']): ?> · <a href="<?= e($sh['tracking_url']) ?>" target="_blank" rel="noopener">Track on courier site →</a><?php endif; ?></div>
    </div>
    <?php endforeach; endif; ?>

    <?php if(!empty($order['timeline'])): ?>
    <div class="timeline-card">
      <div class="timeline-title">📍 Order Status Timeline</div>
      <div class="timeline">
        <?php foreach($order['timeline'] as $tl): ?>
        <div class="tl-item done">
          <div class="tl-dot">✓</div>
          <div class="tl-status"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></div>
          <?php if(!empty($tl['note'])): ?><div class="tl-note"><?= e($tl['note']) ?></div><?php endif; ?>
          <div class="tl-time"><?= formatDateTime($tl['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php $remaining = array_diff(['placed','processing','shipped','delivered'],array_column($order['timeline'],'status'));
        foreach($remaining as $s): ?>
        <div class="tl-item">
          <div class="tl-dot"></div>
          <div class="tl-status pending-status"><?= ucfirst(str_replace('_',' ',$s)) ?></div>
          <div class="tl-time">Pending</div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="track-actions">
      <a href="<?= APP_URL ?>/track" class="btn-outline-link">Track Another Order</a>
      <?php if(\App\Core\Auth::isUserLoggedIn()): ?><a href="<?= APP_URL ?>/account/orders" class="btn-filled-link">My Orders</a><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php $scripts = '<script src="'.asset('frontend/js/track.js').'"></script>'; ?>
