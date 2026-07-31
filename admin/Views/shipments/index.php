<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Shipments</h5><small class="text-muted"><?= number_format($shipments['total']) ?> total</small></div>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Tracking or order #..." value="<?= e($filters['search'] ?? '') ?>"></div>
  <div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()">
    <option value="">All Status</option>
    <?php foreach(['pending','picked_up','in_transit','out_for_delivery','delivered','failed','rto'] as $s): ?>
    <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
    <?php endforeach; ?>
  </select></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/shipments') ?></th><th>Order</th><th>Seller</th><th>Courier</th><th>Tracking #</th><th><?= sortHeader('Status','status',$p.'/shipments') ?></th><th><?= sortHeader('Created','created_at',$p.'/shipments') ?></th><th></th></tr></thead>
  <tbody>
  <?php if(empty($shipments['data'])): ?>
    <tr><td colspan="8"><div class="empty-state"><i class="bi bi-truck"></i><h6>No shipments yet</h6></div></td></tr>
  <?php else: foreach($shipments['data'] as $sh): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;">#<?= $sh['id'] ?></td>
    <td><?= e($sh['order_number']) ?></td>
    <td><?= e($sh['shop_name'] ?? $sh['seller_name']) ?></td>
    <td><?= e($sh['courier_name']) ?></td>
    <td><code><?= e($sh['tracking_number']) ?></code></td>
    <td><?= statusBadge($sh['status']) ?></td>
    <td style="font-size:11.5px;"><?= formatDateTime($sh['created_at']) ?></td>
    <td>
      <?php if(!in_array($sh['status'],['delivered','failed','rto'])): ?>
      <select class="form-select form-select-sm d-inline-block w-auto" onchange="advanceShipment(<?= $sh['id'] ?>, this.value)">
        <option value="">Update status...</option>
        <?php foreach(['picked_up','in_transit','out_for_delivery','delivered','failed','rto'] as $s): if($s===$sh['status']) continue; ?>
        <option value="<?= $s ?>"><?= ucwords(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($shipments['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($shipments,$p.'/shipments') ?></div><?php endif; ?>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function advanceShipment(id, status){
  if(!status) return;
  const fd = new FormData(); fd.append('_csrf_token',CSRF_TOKEN); fd.append('status',status);
  fetch('<?= $p ?>/shipments/'+id+'/track', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Failed'); });
}
</script>
