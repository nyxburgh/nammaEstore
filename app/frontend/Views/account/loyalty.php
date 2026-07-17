<?php include __DIR__.'/_sidebar.php'; ?>
<link rel="stylesheet" href="<?= asset('frontend/css/account.css') ?>">

<div class="loyalty-hero">
  <div class="label">Your Loyalty Points</div>
  <div class="amount">⭐ <?= number_format($loyalty['balance']) ?></div>
  <div class="value">Worth approximately <?= currency($rupeeValue) ?> — redeemable at checkout (min <?= $minRedeem ?> points)</div>
  <div class="sub">
    <div>Total Earned: <?= number_format($loyalty['total_earned']) ?></div>
    <div>Total Redeemed: <?= number_format($loyalty['total_redeemed']) ?></div>
  </div>
</div>

<div class="card">
  <div class="card-head"><span class="card-title">📜 Points History</span></div>
  <div class="card-body no-pad">
    <?php if(empty($transactions['data'])): ?>
    <div class="empty-panel"><div class="empty-icon">⭐</div><p>No points activity yet. Earn points automatically when your orders are delivered.</p></div>
    <?php else: foreach($transactions['data'] as $t): ?>
    <div class="wtx-row">
      <div>
        <div class="wtx-desc"><?= e($t['description']) ?></div>
        <div class="wtx-meta"><?= formatDate($t['created_at'],'d M Y, h:i A') ?></div>
      </div>
      <div class="wtx-amt <?= $t['points']>0?'credit':'debit' ?>">
        <?= $t['points']>0?'+':'' ?><?= $t['points'] ?> pts
      </div>
    </div>
    <?php endforeach; endif; ?>
  </div>
</div>
    </div></div></div>
