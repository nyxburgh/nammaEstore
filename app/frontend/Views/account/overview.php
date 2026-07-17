<?php include __DIR__.'/_sidebar.php'; ?>

<div class="stats-grid">
  <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-val"><?= $stats['total_orders'] ?></div><div class="stat-lbl">Total Orders</div></div>
  <div class="stat-card"><div class="stat-icon">⏳</div><div class="stat-val"><?= $stats['pending_orders'] ?></div><div class="stat-lbl">Active Orders</div></div>
  <div class="stat-card"><div class="stat-icon">❤️</div><div class="stat-val"><?= $stats['wishlist_count'] ?></div><div class="stat-lbl">Wishlist Items</div></div>
  <div class="stat-card"><div class="stat-icon">💸</div><div class="stat-val"><?= currency($stats['total_spent']) ?></div><div class="stat-lbl">Total Spent</div></div>
</div>

<div class="card">
  <div class="card-head"><span class="card-title">📦 Recent Orders</span><a href="<?= APP_URL ?>/account/orders" class="card-link">View All →</a></div>
  <div class="card-body tight">
    <?php if(empty($recentOrders['data'])): ?>
    <div class="acc-empty sm"><div class="ae-icon">📦</div><p>No orders yet. <a href="<?= APP_URL ?>/products" class="inline-link">Start shopping!</a></p></div>
    <?php else: foreach($recentOrders['data'] as $o): ?>
    <div class="order-mini">
      <div><div class="om-num">#<?= e($o['order_number']) ?></div><div class="om-date"><?= formatDate($o['placed_at']) ?> · <?= $o['item_count'] ?> item<?= $o['item_count']>1?'s':'' ?></div></div>
      <span class="status-badge status-<?= e($o['order_status']) ?>"><?= ucfirst($o['order_status']) ?></span>
      <div class="om-total"><?= currency($o['total']) ?></div>
      <a href="<?= APP_URL ?>/account/orders/<?= $o['id'] ?>" class="mini-link">View →</a>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-head"><span class="card-title">⚡ Quick Actions</span></div>
  <div class="card-body">
    <div class="quick-links">
      <?php foreach([[APP_URL.'/account/orders','📦','My Orders'],[APP_URL.'/account/wishlist','❤️','Wishlist'],[APP_URL.'/account/addresses','📍','Addresses'],[APP_URL.'/account/payments','💳','Payments'],[APP_URL.'/track','🔍','Track Order'],[APP_URL.'/account/profile','⚙️','Settings']] as [$href,$ic,$lbl]): ?>
      <a href="<?= $href ?>" class="ql-card"><div class="ql-icon"><?= $ic ?></div><div class="ql-label"><?= $lbl ?></div></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

    </div><!-- .acc-main -->
  </div><!-- .acc-layout -->
</div>
