<?php $p = ADMIN_URL; ?>
<!-- Row 1: KPIs -->
<div class="row g-3 mb-3">
  <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="si purple"><i class="bi bi-currency-rupee"></i></div><div><div class="stat-label">Total Revenue</div><div class="stat-val"><?= currency($stats['total_revenue']) ?></div><div class="stat-sub">Today: <span class="up"><?= currency($stats['revenue_today']) ?></span></div></div></div></div>
  <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="si pink"><i class="bi bi-bag-check-fill"></i></div><div><div class="stat-label">Total Orders</div><div class="stat-val"><?= number_format($stats['total_orders']) ?></div><div class="stat-sub">Today: <span class="up"><?= $stats['orders_today'] ?></span></div></div></div></div>
  <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="si green"><i class="bi bi-shop"></i></div><div><div class="stat-label">Active Sellers</div><div class="stat-val"><?= number_format($stats['active_sellers']) ?></div><div class="stat-sub">Pending: <span class="dn"><?= $stats['pending_sellers'] ?></span></div></div></div></div>
  <div class="col-xl-3 col-md-6"><div class="stat-card"><div class="si blue"><i class="bi bi-people-fill"></i></div><div><div class="stat-label">Customers</div><div class="stat-val"><?= number_format($stats['total_customers']) ?></div><div class="stat-sub">New this month: <span class="up"><?= $stats['new_users_month'] ?></span></div></div></div></div>
</div>
<!-- Row 2: Order status mini chips -->
<div class="row g-2 mb-3">
  <?php if(!empty($stats['pending_reviews'])): ?>
<div class="col-6 col-sm-4 col-lg-2"><div class="card mini-stat" style="border-color:#fde68a!important;">
  <div class="si orange"><i class="bi bi-star-fill"></i></div>
  <div><div class="stat-label">Pending Reviews</div><div class="stat-val"><?= $stats['pending_reviews'] ?></div></div>
  <a href="<?= $p ?>/reviews?status=pending" class="btn btn-sm btn-warning ms-auto" style="font-size:11px;padding:3px 10px;">Review</a>
</div></div>
<?php endif; ?>
<?php foreach([['Placed',$stats['orders_placed'],'orange','hourglass-split'],['Processing',$stats['orders_processing'],'purple','gear'],['Shipped',$stats['orders_shipped'],'blue','truck'],['Delivered',$stats['orders_delivered'],'green','check-circle'],['Cancelled',$stats['orders_cancelled'],'red','x-circle'],['Products',$stats['active_products'],'teal','box-seam']] as [$l,$v,$c,$i]): ?>
  <div class="col-6 col-sm-4 col-lg-2"><div class="card mini-stat"><div class="si <?= $c ?>"><i class="bi bi-<?= $i ?>"></i></div><div><div class="stat-label"><?= $l ?></div><div class="stat-val"><?= number_format($v) ?></div></div></div></div>
  <?php endforeach; ?>
</div>
<!-- Row 3: Chart + Commission -->
<div class="row g-3 mb-3">
  <div class="col-xl-8"><div class="card h-100"><div class="card-header"><span class="card-title">Revenue — Last 6 Months</span></div><div class="card-body"><canvas id="revChart" height="110"></canvas></div></div></div>
  <div class="col-xl-4"><div class="card h-100"><div class="card-header"><span class="card-title">This Month's Commission</span></div>
    <div class="card-body d-flex flex-column align-items-center justify-content-center">
      <div style="font-size:10px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Total Collected</div>
      <div style="font-size:32px;font-weight:800;background:var(--grad);-webkit-background-clip:text;-webkit-text-fill-color:transparent;margin-bottom:14px;"><?= currency((float)($commission['total_commission']??0)) ?></div>
      <div class="w-100">
        <?php foreach([['Seller Earnings',currency((float)($commission['total_seller_earning']??0)),'#16a34a'],['Avg Commission',number_format((float)($commission['avg_commission_pct']??0),1).'%','var(--purple)'],['Transactions',number_format((int)($commission['total_transactions']??0)),'var(--text)']] as [$l,$v,$c]): ?>
        <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--border);"><span style="font-size:12.5px;color:var(--muted);"><?= $l ?></span><span style="font-size:13px;font-weight:700;color:<?= $c ?>;"><?= $v ?></span></div>
        <?php endforeach; ?>
      </div>
      <a href="<?= $p ?>/reports/commission" class="btn btn-outline-primary btn-sm w-100 mt-3">Full Report →</a>
    </div>
  </div></div>
</div>
<!-- Row 4: Recent Orders + Pending/Top Sellers -->
<div class="row g-3">
  <div class="col-xl-7"><div class="card">
    <div class="card-header"><span class="card-title">Recent Orders</span><a href="<?= $p ?>/orders" class="btn btn-sm btn-outline-primary">View All</a></div>
    <div class="table-responsive"><table class="table mb-0">
      <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
      <tbody>
      <?php if(empty($recentOrders)): ?><tr><td colspan="6"><div class="empty-state" style="padding:30px;"><i class="bi bi-bag-x"></i><h6>No orders yet</h6></div></td></tr>
      <?php else: foreach($recentOrders as $o): ?>
      <tr>
        <td><a href="<?= $p ?>/orders/<?= $o['id'] ?>" style="font-weight:700;color:var(--purple);text-decoration:none;"><?= e($o['order_number']) ?></a></td>
        <td><?= e($o['customer_name']) ?></td>
        <td style="font-weight:700;"><?= currency($o['total']) ?></td>
        <td><?= statusBadge($o['order_status']) ?></td>
        <td><?= statusBadge($o['payment_status']) ?></td>
        <td style="font-size:11.5px;color:var(--muted);"><?= formatDate($o['placed_at'],'d M') ?></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table></div>
  </div></div>
  <div class="col-xl-5 d-flex flex-column gap-3">
    <?php if(!empty($pendingSellers)): ?>
    <div class="card"><div class="card-header"><span class="card-title">Pending Sellers <span class="badge badge-warning ms-1"><?= count($pendingSellers) ?></span></span><a href="<?= $p ?>/sellers?status=pending" class="btn btn-sm btn-outline-primary">All</a></div>
      <?php foreach($pendingSellers as $v): ?>
      <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom" style="border-color:var(--border)!important;">
        <div class="d-flex align-items-center gap-2"><div class="av av-sm"><?= strtoupper(substr($v['name'],0,1)) ?></div><div><div style="font-size:13px;font-weight:600;"><?= e($v['name']) ?></div><div style="font-size:11px;color:var(--muted);"><?= e($v['shop_name']) ?></div></div></div>
        <div class="d-flex gap-1">
          <form method="post" action="<?= $p ?>/sellers/<?= $v['id'] ?>/approve" style="display:inline;">
  <?= csrf_field() ?><button class="btn btn-sm btn-success" style="padding:3px 10px;font-size:12px;border-radius:6px;">✓</button></form>
          <a href="<?= $p ?>/sellers/<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary" style="padding:3px 8px;font-size:12px;border-radius:6px;">View</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="card flex-fill"><div class="card-header"><span class="card-title">Top Sellers</span></div>
      <?php if(empty($topSellers)): ?><div class="card-body"><div class="empty-state" style="padding:20px;"><i class="bi bi-shop"></i><h6>No sales yet</h6></div></div>
      <?php else: foreach($topSellers as $i=>$v): ?>
      <div class="d-flex align-items-center justify-content-between px-3 py-2 <?= $i<count($topSellers)-1?'border-bottom':'' ?>" style="border-color:var(--border)!important;">
        <div class="d-flex align-items-center gap-2"><span style="width:20px;text-align:center;font-size:12px;font-weight:700;color:var(--muted);"><?= $i+1 ?></span><div class="av av-sm"><?= strtoupper(substr($v['shop_name']??$v['name'],0,1)) ?></div><div><div style="font-size:13px;font-weight:600;"><?= e($v['shop_name']??$v['name']) ?></div><div style="font-size:11px;color:var(--muted);"><?= $v['order_count'] ?> orders</div></div></div>
        <span style="font-size:13px;font-weight:700;color:var(--purple);"><?= currency($v['total_sales']) ?></span>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<?php
$labels = json_encode($revenueChart['labels']);
$rev    = json_encode($revenueChart['revenue']);
$ords   = json_encode($revenueChart['orders']);
$scripts = <<<JS
<script>
new Chart(document.getElementById('revChart').getContext('2d'),{
  data:{labels:{$labels},datasets:[
    {type:'bar',label:'Revenue (₹)',data:{$rev},backgroundColor:'rgba(109,40,217,.12)',borderColor:'#6d28d9',borderWidth:2,borderRadius:8,yAxisID:'y'},
    {type:'line',label:'Orders',data:{$ords},borderColor:'#e91e8c',backgroundColor:'transparent',borderWidth:2.5,tension:.4,pointBackgroundColor:'#e91e8c',pointRadius:4,yAxisID:'y1'}
  ]},
  options:{responsive:true,interaction:{mode:'index',intersect:false},
    plugins:{legend:{position:'bottom',labels:{font:{family:'Plus Jakarta Sans',size:11},usePointStyle:true}}},
    scales:{y:{position:'left',grid:{color:'rgba(109,40,217,.05)'},ticks:{callback:v=>'₹'+v.toLocaleString('en-IN'),font:{size:11}}},y1:{position:'right',grid:{display:false},ticks:{font:{size:11}}},x:{grid:{display:false},ticks:{font:{size:11}}}}}
});
</script>
JS; ?>
