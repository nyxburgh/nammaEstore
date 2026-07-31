<?php include __DIR__.'/_sidebar.php'; ?>
<link rel="stylesheet" href="<?= asset('frontend/css/account.css') ?>">

<div class="wallet-hero">
  <div class="label">Available Balance</div>
  <div class="amount"><?= currency($wallet['balance']) ?></div>
  <div class="wallet-sub">
    <div>Total Added: <?= currency($wallet['total_added']) ?></div>
    <div>Total Used: <?= currency($wallet['total_used']) ?></div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span class="card-title">📜 Transaction History</span></div>
  <div class="card-body no-pad">
    <?php if(empty($transactions['data'])): ?>
    <div class="empty-panel"><div class="empty-icon">👛</div><p>No wallet activity yet. Your wallet is credited from refunds and can be used at checkout.</p></div>
    <?php else: foreach($transactions['data'] as $t): ?>
    <div class="wtx-row">
      <div>
        <div class="wtx-desc"><?= e($t['description']) ?></div>
        <div class="wtx-meta"><?= formatDate($t['created_at'],'d M Y, h:i A') ?></div>
      </div>
      <div class="wtx-amt <?= $t['type']==='credit'?'credit':'debit' ?>">
        <?= $t['type']==='credit'?'+':'-' ?><?= currency($t['amount']) ?>
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
    </div></div></div>
