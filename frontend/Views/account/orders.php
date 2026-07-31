<?php include __DIR__.'/_sidebar.php'; ?>
<div class="filter-tabs">
  <?php foreach([['','All'],['placed','Placed'],['processing','Processing'],['shipped','Shipped'],['delivered','Delivered'],['cancelled','Cancelled']] as [$s,$l]): ?>
  <a href="?status=<?= $s ?>" class="ftab <?= $status===$s?'active':'' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div>
<div class="card">
  <div class="card-head"><span class="card-title">📦 My Orders</span><span class="head-count"><?= $orders['total'] ?> order<?= $orders['total']!=1?'s':'' ?></span></div>
  <div class="card-body flush">
    <?php if(empty($orders['data'])): ?>
    <div class="acc-empty"><div class="ae-icon">📦</div><p>No orders found.</p><a href="<?= APP_URL ?>/products" class="ae-btn">Start Shopping</a></div>
    <?php else: foreach($orders['data'] as $o): ?>
    <div class="order-card flat">
      <div class="oc-head">
        <div><div class="oc-num">Order #<?= e($o['order_number']) ?></div><div class="oc-date"><?= formatDate($o['placed_at']) ?> · <?= $o['item_count'] ?> item<?= $o['item_count']>1?'s':'' ?></div></div>
        <span class="status-badge status-<?= e($o['order_status']) ?>"><?= ucfirst($o['order_status']) ?></span>
      </div>
      <div class="oc-foot">
        <div class="oc-total"><?= currency($o['total']) ?></div>
        <div class="oc-actions">
          <a href="<?= APP_URL ?>/track/<?= urlencode($o['order_number']) ?>" class="btn-track">📍 Track</a>
          <a href="<?= APP_URL ?>/account/orders/<?= $o['id'] ?>" class="btn-view">View Details</a>
        </div>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
<?php if($orders['total_pages']>1): $cp=$orders['current_page']; $tp=$orders['total_pages']; ?>
<div class="pagination">
  <a href="?status=<?= $status ?>&page=<?= max(1,$cp-1) ?>" class="page-link <?= $cp<=1?'disabled':'' ?>">‹</a>
  <?php for($i=max(1,$cp-2);$i<=min($tp,$cp+2);$i++): ?><a href="?status=<?= $status ?>&page=<?= $i ?>" class="page-link <?= $i===$cp?'active':'' ?>"><?= $i ?></a><?php endfor; ?>
  <a href="?status=<?= $status ?>&page=<?= min($tp,$cp+1) ?>" class="page-link <?= $cp>=$tp?'disabled':'' ?>">›</a>
</div>
<?php endif; ?>
    </div></div></div>
