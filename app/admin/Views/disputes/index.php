<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Disputes</h5><small class="text-muted"><?= number_format($disputes['total']) ?> total</small></div>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Order # or seller..." value="<?= e($filters['search']??'') ?>"></div>
  <div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()">
    <option value="open" <?= ($filters['status']??'')==='open'?'selected':'' ?>>Open</option>
    <option value="resolved" <?= ($filters['status']??'')==='resolved'?'selected':'' ?>>Resolved</option>
    <option value="">All</option>
  </select></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/disputes') ?></th><th>Order</th><th>Seller</th><th>Raised By</th><th>Reason</th><th><?= sortHeader('Status','status',$p.'/disputes') ?></th><th><?= sortHeader('Opened','created_at',$p.'/disputes') ?></th><th></th></tr></thead>
  <tbody>
  <?php if(empty($disputes['data'])): ?>
    <tr><td colspan="8"><div class="empty-state"><i class="bi bi-exclamation-triangle"></i><h6>No disputes</h6></div></td></tr>
  <?php else: foreach($disputes['data'] as $d): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;">#<?= $d['id'] ?></td>
    <td><?= e($d['order_number']) ?></td>
    <td><?= e($d['seller_name']) ?></td>
    <td><?= ucfirst($d['raised_by']) ?></td>
    <td style="max-width:220px;font-size:12px;"><?= e($d['reason']) ?></td>
    <td><?= statusBadge($d['status']) ?></td>
    <td style="font-size:11.5px;color:var(--muted);"><?= formatDateTime($d['created_at']) ?></td>
    <td>
      <?php if($d['status']==='open'): ?>
      <button class="btn btn-sm btn-success" onclick="resolveDispute(<?= $d['id'] ?>)">Resolve</button>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($disputes['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($disputes,$p.'/disputes') ?></div><?php endif; ?>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function resolveDispute(id){
  const note = prompt('Resolution note:');
  if(note===null) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN); fd.append('note', note);
  fetch('<?= $p ?>/disputes/'+id+'/resolve', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Action failed.'); });
}
</script>
