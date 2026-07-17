<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Withdrawal Requests</h5><small class="text-muted"><?= number_format($withdrawals['total']) ?> total</small></div>
  <a href="<?= $p ?>/settlements" class="btn btn-outline-secondary btn-sm">← Settlement Engine</a>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-3"><select name="status" class="form-select" onchange="this.form.submit()">
    <option value="">All Status</option>
    <?php foreach(['pending','approved','rejected','paid'] as $s): ?>
    <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th>Vendor</th><th>Amount</th><th>Method</th><th>Status</th><th>Requested</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($withdrawals['data'])): ?>
    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-wallet2"></i><h6>No withdrawal requests</h6></div></td></tr>
  <?php else: foreach($withdrawals['data'] as $w): ?>
  <tr>
    <td><div style="font-weight:600;font-size:13px;"><?= e($w['shop_name'] ?? $w['vendor_name']) ?></div><div style="font-size:11px;color:var(--muted);"><?= e($w['vendor_name']) ?></div></td>
    <td style="font-weight:700;"><?= currency($w['amount']) ?></td>
    <td><?= ucwords(str_replace('_',' ',$w['method'])) ?><?php if($w['method_details']): ?><div style="font-size:11px;color:var(--muted);"><?= e($w['method_details']) ?></div><?php endif; ?></td>
    <td><?= statusBadge($w['status']) ?></td>
    <td style="font-size:11.5px;color:var(--muted);"><?= formatDateTime($w['requested_at']) ?></td>
    <td>
      <?php if($w['status']==='pending'): ?>
      <button class="btn btn-sm btn-success" onclick="wAction(<?= $w['id'] ?>,'approve')"><i class="bi bi-check-lg"></i></button>
      <button class="btn btn-sm btn-outline-danger" onclick="wAction(<?= $w['id'] ?>,'reject')"><i class="bi bi-x-lg"></i></button>
      <?php elseif($w['status']==='approved'): ?>
      <button class="btn btn-sm btn-primary" onclick="wAction(<?= $w['id'] ?>,'mark-paid')">Mark Paid</button>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($withdrawals['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($withdrawals,$p.'/settlements/withdrawals') ?></div><?php endif; ?>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function wAction(id, action){
  if(action==='reject' && !confirm('Reject this withdrawal request?')) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN);
  if(action==='reject'){ const note = prompt('Reason for rejection (optional):') || ''; fd.append('note', note); }
  fetch('<?= $p ?>/settlements/withdrawals/'+id+'/'+action, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{
      if(d.success) location.reload();
      else alert(d.message || 'Action failed.');
    });
}
</script>
