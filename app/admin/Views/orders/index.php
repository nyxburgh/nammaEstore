<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3"><div><h5 style="font-weight:800;margin:0;">Orders</h5><small class="text-muted"><?= number_format($orders['total']) ?> total</small></div></div>
<div class="row g-2 mb-3">
  <?php foreach([['Revenue',currency((float)$stats['revenue']),'purple','currency-rupee'],['Today',$stats['today'],'pink','calendar-check'],['Pending',$stats['placed'],'orange','hourglass-split'],['Shipped',$stats['shipped'],'blue','truck'],['Delivered',$stats['delivered'],'green','check-circle'],['Cancelled',$stats['cancelled'],'red','x-circle']] as [$l,$v,$c,$i]): ?>
  <div class="col-6 col-sm-4 col-lg-2"><div class="card mini-stat"><div class="si <?= $c ?>"><i class="bi bi-<?= $i ?>"></i></div><div><div class="stat-label"><?= $l ?></div><div class="stat-val"><?= $v ?></div></div></div></div>
  <?php endforeach; ?>
</div>
<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end" onsubmit="return validateForm(this)">
  <div class="col-md-3"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Order # or customer..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-2"><select name="status" class="form-select"><option value="">All Status</option><?php foreach(['placed','confirmed','processing','shipped','delivered','cancelled','returned'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><select name="payment_status" class="form-select"><option value="">All Payments</option><?php foreach(['pending','paid','failed','refunded'] as $s): ?><option value="<?= $s ?>" <?= ($filters['payment_status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><input type="date" name="date_from" id="ordersDateFrom" class="form-control" value="<?= e($filters['date_from']??'') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-2"><input type="date" name="date_to" class="form-control" data-gte-field="#ordersDateFrom" value="<?= e($filters['date_to']??'') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-1"><button class="btn btn-primary w-100">Go</button></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/orders') ?></th><th><?= sortHeader('Order #','order_number',$p.'/orders') ?></th><th>Customer</th><th>Items</th><th><?= sortHeader('Total','total',$p.'/orders') ?></th><th>Payment</th><th>Status</th><th><?= sortHeader('Date','placed_at',$p.'/orders') ?></th><th></th></tr></thead>
  <tbody>
  <?php if(empty($orders['data'])): ?><tr><td colspan="9"><div class="empty-state"><i class="bi bi-bag-x"></i><h6>No orders</h6></div></td></tr>
  <?php else: foreach($orders['data'] as $o): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;"><?= $o['id'] ?></td>
    <td><a href="<?= $p ?>/orders/<?= $o['id'] ?>" style="font-weight:700;color:var(--purple);text-decoration:none;"><?= e($o['order_number']) ?></a></td>
    <td><div style="font-weight:600;font-size:13px;"><?= e($o['customer_name']) ?></div><div style="font-size:11px;color:var(--muted);"><?= e($o['customer_email']) ?></div></td>
    <td><?= $o['item_count'] ?> item<?= $o['item_count']!=1?'s':'' ?></td>
    <td style="font-weight:700;"><?= currency($o['total']) ?></td>
    <td><?= statusBadge($o['payment_status']) ?></td>
    <td><?= statusBadge($o['order_status']) ?></td>
    <td style="font-size:11.5px;color:var(--muted);"><?= formatDateTime($o['placed_at']) ?></td>
    <td><a href="<?= $p ?>/orders/<?= $o['id'] ?>" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-eye"></i></a></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($orders['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($orders,$p.'/orders') ?></div><?php endif; ?>
</div>
