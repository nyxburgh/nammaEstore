<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 style="font-weight:800;margin:0;">Gift Voucher: <?= e($card['code']) ?></h5>
  <a href="<?= $p ?>/gift-cards" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:12px;">Type</div><div style="font-weight:700;"><?= ucfirst($card['type']) ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:12px;">Initial Balance</div><div style="font-weight:700;"><?= currency($card['initial_balance']) ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:12px;">Current Balance</div><div style="font-weight:700;color:var(--green,#2e7d32);"><?= currency($card['current_balance']) ?></div></div></div></div>
  <div class="col-md-3"><div class="card"><div class="card-body"><div class="text-muted" style="font-size:12px;">Status</div><div><?= statusBadge($card['status']) ?></div></div></div></div>
</div>

<div class="card"><div class="card-head" style="padding:14px 16px;font-weight:700;border-bottom:1px solid #eee;">Transaction History</div>
<div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Type</th><th>Amount</th><th>Balance After</th><th>Order</th><th>Date</th></tr></thead>
  <tbody>
  <?php foreach(($card['transactions'] ?? []) as $t): ?>
  <tr>
    <td><?= ucfirst($t['type']) ?></td>
    <td><?= currency($t['amount']) ?></td>
    <td><?= currency($t['balance_after']) ?></td>
    <td><?= $t['order_id'] ? '#'.$t['order_id'] : '—' ?></td>
    <td style="font-size:11.5px;"><?= formatDateTime($t['created_at']) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($card['transactions'])): ?>
  <tr><td colspan="5" class="text-center text-muted py-3">No activity yet.</td></tr>
  <?php endif; ?>
  </tbody>
</table></div></div>
