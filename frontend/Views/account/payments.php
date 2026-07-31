<?php include __DIR__.'/_sidebar.php'; ?>
<div class="card">
  <div class="card-head"><span class="card-title">💳 Payment History</span></div>
  <div class="card-body flush">
    <?php if(empty($transactions)): ?>
    <div class="acc-empty"><div class="ae-icon">💳</div><p>No payment records yet.</p></div>
    <?php else: foreach($transactions as $t): ?>
    <div class="txn-row">
      <div class="txn-icon pink">💳</div>
      <div><div class="txn-desc">Order Payment</div><div class="txn-date"><?= formatDateTime($t['created_at']) ?></div></div>
      <div class="txn-amount pink"><?= currency($t['amount']) ?></div>
      <span class="txn-status status-<?= e($t['status']) ?>"><?= ucfirst($t['status']) ?></span>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
    </div></div></div>
