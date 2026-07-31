<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Returns &amp; Replacements</h5><small class="text-muted"><?= number_format($returns['total']) ?> total</small></div>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Order #, product, customer..." value="<?= e($filters['search']??'') ?>"></div>
  <div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()">
    <option value="">All Status</option>
    <?php foreach(['requested','approved','rejected','picked_up','refunded','completed'] as $s): ?>
    <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="col-md-3"><select name="type" class="form-select" onchange="this.form.submit()">
    <option value="">All Types</option>
    <?php foreach(['return','replacement','cancel'] as $t): ?>
    <option value="<?= $t ?>" <?= ($filters['type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
    <?php endforeach; ?>
  </select></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/returns') ?></th><th>Order</th><th>Product</th><th>Customer</th><th>Seller</th><th><?= sortHeader('Type','type',$p.'/returns') ?></th><th>Reason</th><th><?= sortHeader('Status','status',$p.'/returns') ?></th><th><?= sortHeader('Requested','requested_at',$p.'/returns') ?></th><th></th></tr></thead>
  <tbody>
  <?php if(empty($returns['data'])): ?>
    <tr><td colspan="10"><div class="empty-state"><i class="bi bi-arrow-return-left"></i><h6>No return requests</h6></div></td></tr>
  <?php else: foreach($returns['data'] as $r): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;">#<?= $r['id'] ?></td>
    <td><?= e($r['order_number']) ?></td>
    <td><?= e($r['product_name']) ?></td>
    <td><?= e($r['customer_name']) ?></td>
    <td><?= e($r['seller_name']) ?></td>
    <td><?= ucfirst($r['type']) ?></td>
    <td style="max-width:180px;font-size:12px;"><?= e($r['reason']) ?></td>
    <td><?= statusBadge($r['status']) ?></td>
    <td style="font-size:11.5px;color:var(--muted);"><?= formatDateTime($r['requested_at']) ?></td>
    <td>
      <?php if($r['status']==='requested'): ?>
      <button class="btn btn-sm btn-success" onclick="rAction(<?= $r['id'] ?>,'approve')"><i class="bi bi-check-lg"></i></button>
      <button class="btn btn-sm btn-outline-danger" onclick="rAction(<?= $r['id'] ?>,'reject')"><i class="bi bi-x-lg"></i></button>
      <?php elseif(in_array($r['status'],['approved','picked_up'])): ?>
      <button class="btn btn-sm btn-primary" onclick="rRefund(<?= $r['id'] ?>)">Mark Refunded</button>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($returns['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($returns,$p.'/returns') ?></div><?php endif; ?>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function rAction(id, action){
  if(action==='reject' && !confirm('Reject this request?')) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN);
  fetch('<?= $p ?>/returns/'+id+'/'+action, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Action failed.'); });
}
function rRefund(id){
  const amount = prompt('Refund amount (₹):');
  if(!amount) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN); fd.append('refund_amount', amount);
  fetch('<?= $p ?>/returns/'+id+'/refund', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Action failed.'); });
}
</script>
