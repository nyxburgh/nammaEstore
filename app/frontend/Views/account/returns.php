<?php include __DIR__.'/_sidebar.php'; ?>
<link rel="stylesheet" href="<?= asset('frontend/css/account.css') ?>">

<div class="card">
  <div class="card-head"><span class="card-title">↩️ My Returns</span><span class="card-count-note"><?= $returns['total'] ?> request<?= $returns['total']!=1?'s':'' ?></span></div>
  <div class="card-body no-pad">
    <?php if(empty($returns['data'])): ?>
    <div class="empty-panel"><div class="empty-icon">↩️</div><p>No return requests yet.</p></div>
    <?php else: foreach($returns['data'] as $r): ?>
    <div class="ret-card">
      <div class="ret-head">
        <div class="ret-title"><?= e($r['product_name']) ?></div>
        <span class="status-badge status-<?= e($r['status']) ?>"><?= ucfirst($r['status']) ?></span>
      </div>
      <div class="ret-meta">Order #<?= e($r['order_number']) ?> · <?= ucfirst($r['type']) ?> · Requested <?= formatDate($r['requested_at']) ?></div>
      <div class="ret-meta reason">Reason: <?= e($r['reason']) ?></div>
      <?php if($r['status']==='refunded' || $r['status']==='completed'): ?>
      <div class="ret-meta refunded">Refunded: <?= currency($r['refund_amount']) ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
    </div></div></div>
