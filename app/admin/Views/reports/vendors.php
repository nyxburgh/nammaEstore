<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/reports" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><div><h5 style="font-weight:800;margin:0;">Vendor Performance</h5><small class="text-muted">All-time stats</small></div></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>#</th><th>Vendor</th><th>Plan</th><th>Orders</th><th>Gross Sales</th><th>Commission</th><th>Net Earning</th></tr></thead>
  <tbody>
  <?php if(empty($data)): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-shop"></i><h6>No data yet</h6></div></td></tr>
  <?php else: foreach($data as $i=>$r): ?><tr>
    <td style="font-weight:700;color:var(--muted);"><?= $i+1 ?></td>
    <td><div class="d-flex align-items-center gap-2"><div class="av av-sm"><?= strtoupper(substr($r['shop_name']??$r['name'],0,1)) ?></div><div><div style="font-weight:600;"><?= e($r['shop_name']??'—') ?></div><div style="font-size:12px;color:var(--muted);"><?= e($r['name']) ?></div></div></div></td>
    <td><span class="badge badge-primary"><?= e($r['plan_name']??'Free') ?></span></td>
    <td style="font-weight:600;"><?= number_format($r['total_orders']) ?></td>
    <td style="font-weight:700;"><?= currency($r['gross_sales']) ?></td>
    <td style="color:var(--pink);font-weight:600;"><?= currency($r['commission']) ?></td>
    <td style="color:#16a34a;font-weight:700;"><?= currency($r['earning']) ?></td>
  </tr><?php endforeach; endif; ?>
  </tbody>
</table></div></div>
