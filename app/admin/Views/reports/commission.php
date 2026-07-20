<?php $p=ADMIN_URL; $tc=array_sum(array_column($data??[],'total_commission')); $te=array_sum(array_column($data??[],'total_earning')); $tg=array_sum(array_column($data??[],'gross_amount')); ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/reports" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><div><h5 style="font-weight:800;margin:0;">Commission Report</h5><small class="text-muted"><?= formatDate($from) ?> — <?= formatDate($to) ?></small></div></div>
<div class="filter-bar mb-4"><form method="get" class="row g-2 align-items-end" onsubmit="return validateForm(this)">
  <div class="col-md-3"><label class="form-label">From</label><input type="date" name="date_from" id="commDateFrom" class="form-control" value="<?= e($from) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-3"><label class="form-label">To</label><input type="date" name="date_to" class="form-control" data-gte-field="#commDateFrom" value="<?= e($to) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-2"><button class="btn btn-primary mt-4">Generate</button></div>
</form></div>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="stat-card"><div class="si pink"><i class="bi bi-percent"></i></div><div><div class="stat-label">Commission Collected</div><div class="stat-val"><?= currency($tc) ?></div></div></div></div>
  <div class="col-md-4"><div class="stat-card"><div class="si green"><i class="bi bi-wallet"></i></div><div><div class="stat-label">Vendor Earnings</div><div class="stat-val"><?= currency($te) ?></div></div></div></div>
  <div class="col-md-4"><div class="stat-card"><div class="si purple"><i class="bi bi-currency-rupee"></i></div><div><div class="stat-label">Gross Sales</div><div class="stat-val"><?= currency($tg) ?></div></div></div></div>
</div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Vendor</th><th>Plan</th><th>Gross</th><th>Commission</th><th>Net Earning</th><th>Transactions</th></tr></thead>
  <tbody>
  <?php if(empty($data)): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-percent"></i><h6>No data</h6></div></td></tr>
  <?php else: foreach($data as $r): ?><tr>
    <td><div style="font-weight:600;"><?= e($r['shop_name']??$r['vendor_name']) ?></div><div style="font-size:12px;color:var(--muted);"><?= e($r['vendor_name']) ?></div></td>
    <td><span class="badge badge-primary"><?= e($r['plan_name']) ?></span></td>
    <td style="font-weight:600;"><?= currency($r['gross_amount']) ?></td>
    <td style="font-weight:700;color:var(--pink);"><?= currency($r['total_commission']) ?></td>
    <td style="font-weight:700;color:#16a34a;"><?= currency($r['total_earning']) ?></td>
    <td><?= number_format($r['transactions']) ?></td>
  </tr><?php endforeach; endif; ?>
  </tbody>
</table></div></div>
