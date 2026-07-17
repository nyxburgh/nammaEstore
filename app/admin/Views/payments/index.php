<?php $p = ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Payments</h5><small class="text-muted"><?= number_format($payments['total']) ?> transactions</small></div>
</div>

<!-- Totals strip -->
<div class="row g-2 mb-3">
  <?php foreach([
    ['Collected',   currency((float)($totals['collected']??0)),    'green',  'check-circle-fill'],
    ['Pending',     currency((float)($totals['pending_amt']??0)),  'orange', 'hourglass-split'],
    ['Failed',      number_format((int)($totals['failed_count']??0)), 'red', 'x-circle-fill'],
    ['Total Txns',  number_format((int)($totals['total']??0)),     'purple', 'credit-card-fill'],
  ] as [$l,$v,$c,$i]): ?>
  <div class="col-md-3 col-6">
    <div class="card" style="padding:12px 16px;display:flex;flex-direction:row;align-items:center;gap:10px;">
      <div class="si <?= $c ?>" style="width:36px;height:36px;font-size:13px;border-radius:9px;flex-shrink:0;"><i class="bi bi-<?= $i ?>"></i></div>
      <div><div class="stat-label" style="font-size:10.5px;"><?= $l ?></div><div class="stat-val" style="font-size:17px;"><?= $v ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="filter-bar mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Order number or customer..." value="<?= e($filters['search']) ?>"></div></div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <?php foreach(['pending','success','failed','refunded'] as $s): ?>
        <option value="<?= $s ?>" <?= $filters['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="method" class="form-select">
        <option value="">All Methods</option>
        <?php foreach(['cod','upi','card','netbanking'] as $m): ?>
        <option value="<?= $m ?>" <?= $filters['method']===$m?'selected':'' ?>><?= strtoupper($m) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-primary flex-fill">Filter</button>
      <a href="<?= $p ?>/payments" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th><?= sortHeader('#','id',$p.'/payments') ?></th><th>Transaction</th><th>Order</th><th>Customer</th><th>Method</th><th><?= sortHeader('Amount','amount',$p.'/payments') ?></th><th><?= sortHeader('Status','status',$p.'/payments') ?></th><th><?= sortHeader('Date','created_at',$p.'/payments') ?></th><th>Actions</th></tr></thead>
      <tbody>
      <?php if(empty($payments['data'])): ?>
        <tr><td colspan="9"><div class="empty-state"><i class="bi bi-credit-card"></i><h6>No payments found</h6></div></td></tr>
      <?php else: foreach($payments['data'] as $pay): ?>
      <tr>
        <td style="color:var(--muted);font-size:12px;">#<?= $pay['id'] ?></td>
        <td style="font-size:12px;color:var(--muted);font-family:monospace;"><?= $pay['transaction_id'] ? e(substr($pay['transaction_id'],0,16)).'…' : '—' ?></td>
        <td><a href="<?= $p ?>/orders/<?= $pay['order_id'] ?>" style="font-weight:700;color:var(--purple);text-decoration:none;"><?= e($pay['order_number']) ?></a></td>
        <td>
          <div style="font-weight:600;font-size:13px;"><?= e($pay['customer_name']) ?></div>
          <div style="font-size:11px;color:var(--muted);"><?= e($pay['customer_email']) ?></div>
        </td>
        <td><span class="badge badge-secondary" style="text-transform:uppercase;"><?= e($pay['payment_method']) ?></span></td>
        <td style="font-weight:700;font-size:14px;"><?= currency($pay['amount']) ?></td>
        <td>
          <select class="form-select form-select-sm pay-status" style="width:110px;font-size:12px;" data-id="<?= $pay['id'] ?>">
            <?php foreach(['pending','success','failed','refunded'] as $s): ?>
            <option value="<?= $s ?>" <?= $pay['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </td>
        <td style="font-size:12px;white-space:nowrap;">
          <?= formatDate($pay['created_at']) ?>
          <?php if($pay['paid_at']): ?><div style="font-size:11px;color:var(--muted);">Paid: <?= formatDate($pay['paid_at']) ?></div><?php endif; ?>
        </td>
        <td><a href="<?= $p ?>/orders/<?= $pay['order_id'] ?>" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-eye"></i></a></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($payments['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($payments,$p.'/payments') ?></div><?php endif; ?>
</div>

<?php $scripts = <<<'JS'
<script>
document.querySelectorAll('.pay-status').forEach(s => {
  s.addEventListener('change', function() {
    fetch(ADMIN_URL+'/payments/'+this.dataset.id+'/status', {
      method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'status='+this.value+'&_csrf_token='+CSRF_TOKEN
    }).then(r=>r.json()).then(d=>{
      if(d.success) {
        const row = this.closest('tr');
        row.style.background='rgba(22,163,74,.06)';
        setTimeout(()=>row.style.background='',800);
      }
    });
  });
});
</script>
JS; ?>
