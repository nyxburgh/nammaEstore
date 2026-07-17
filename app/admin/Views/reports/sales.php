<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/reports" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><div><h5 style="font-weight:800;margin:0;">Sales Report</h5><small class="text-muted"><?= formatDate($from) ?> — <?= formatDate($to) ?></small></div></div>
<div class="filter-bar mb-4"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label">From</label><input type="date" name="date_from" class="form-control" value="<?= e($from) ?>"></div>
  <div class="col-md-3"><label class="form-label">To</label><input type="date" name="date_to" class="form-control" value="<?= e($to) ?>"></div>
  <div class="col-md-2"><button class="btn btn-primary mt-4">Generate</button></div>
</form></div>
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="stat-card"><div class="si purple"><i class="bi bi-currency-rupee"></i></div><div><div class="stat-label">Revenue</div><div class="stat-val"><?= currency((float)($summary['total_revenue']??0)) ?></div></div></div></div>
  <div class="col-md-4"><div class="stat-card"><div class="si pink"><i class="bi bi-bag-check"></i></div><div><div class="stat-label">Orders</div><div class="stat-val"><?= number_format((int)($summary['total_orders']??0)) ?></div></div></div></div>
  <div class="col-md-4"><div class="stat-card"><div class="si green"><i class="bi bi-graph-up"></i></div><div><div class="stat-label">Avg Order</div><div class="stat-val"><?= ($summary['total_orders']>0)?currency((float)$summary['total_revenue']/(int)$summary['total_orders']):'₹0.00' ?></div></div></div></div>
</div>
<?php if(!empty($data)): ?><div class="card mb-4"><div class="card-header"><span class="card-title">Daily Revenue</span></div><div class="card-body"><canvas id="sChart" height="80"></canvas></div></div><?php endif; ?>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Date</th><th>Orders</th><th>Revenue</th><th>Cancelled</th></tr></thead>
  <tbody>
  <?php if(empty($data)): ?><tr><td colspan="4"><div class="empty-state"><i class="bi bi-bar-chart"></i><h6>No data for this period</h6></div></td></tr>
  <?php else: foreach($data as $row): ?><tr><td style="font-weight:600;"><?= formatDate($row['date']) ?></td><td><?= number_format($row['orders']) ?></td><td style="font-weight:700;color:var(--purple);"><?= currency($row['revenue']) ?></td><td><span class="badge badge-danger"><?= $row['cancelled'] ?></span></td></tr><?php endforeach; endif; ?>
  </tbody>
</table></div></div>
<?php if(!empty($data)): $lbs=json_encode(array_column($data,'date')); $rv=json_encode(array_column($data,'revenue'));
$scripts='<script>new Chart(document.getElementById("sChart").getContext("2d"),{type:"line",data:{labels:'.$lbs.',datasets:[{label:"Revenue",data:'.$rv.',borderColor:"#6d28d9",backgroundColor:"rgba(109,40,217,.08)",fill:true,tension:.4,borderWidth:2.5,pointBackgroundColor:"#6d28d9",pointRadius:4}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{grid:{color:"rgba(109,40,217,.05)"},ticks:{callback:v=>"₹"+v.toLocaleString("en-IN")}},x:{grid:{display:false}}}}});</script>';
endif; ?>
