<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Coupons</h5><small class="text-muted"><?= number_format($coupons['total']) ?> total</small></div>
  <a href="<?= $p ?>/coupons/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> New Coupon</a>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Search code..." value="<?= e($filters['search'] ?? '') ?>"></div>
  <div class="col-md-3"><select name="scope" class="form-select">
    <option value="">All Scopes</option>
    <?php foreach(['platform','seller','category','brand','product','user','first_order','festival'] as $s): ?>
    <option value="<?= $s ?>" <?= ($filters['scope']??'')===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
    <?php endforeach; ?>
  </select></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/coupons') ?></th><th><?= sortHeader('Code','code',$p.'/coupons') ?></th><th>Type</th><th><?= sortHeader('Value','value',$p.'/coupons') ?></th><th>Scope</th><th><?= sortHeader('Usage','used_count',$p.'/coupons') ?></th><th><?= sortHeader('Expires','expires_at',$p.'/coupons') ?></th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($coupons['data'])): ?>
    <tr><td colspan="9"><div class="empty-state"><i class="bi bi-ticket-perforated"></i><h6>No coupons yet</h6></div></td></tr>
  <?php else: foreach($coupons['data'] as $c): ?>
  <tr>
    <td>#<?= $c['id'] ?></td>
    <td><code style="font-weight:700;"><?= e($c['code']) ?></code><?php if($c['label']): ?><div style="font-size:11px;color:var(--muted);"><?= e($c['label']) ?></div><?php endif; ?></td>
    <td><?= ucfirst($c['value_type']) ?></td>
    <td><?= $c['value_type']==='percentage' ? $c['value'].'%' : currency($c['value']) ?></td>
    <td><?= ucwords(str_replace('_',' ',$c['scope'])) ?></td>
    <td><?= (int)$c['used_count'] ?><?= $c['usage_limit_total'] ? ' / '.$c['usage_limit_total'] : '' ?></td>
    <td style="font-size:11.5px;"><?= $c['expires_at'] ? formatDate($c['expires_at']) : 'Never' ?></td>
    <td>
      <label class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" <?= $c['is_active']?'checked':'' ?> onchange="toggleCoupon(<?= $c['id'] ?>)">
      </label>
    </td>
    <td>
      <a href="<?= $p ?>/coupons/<?= $c['id'] ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
      <button class="btn btn-sm btn-outline-danger" onclick="deleteCoupon(<?= $c['id'] ?>)"><i class="bi bi-trash"></i></button>
    </td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($coupons['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($coupons,$p.'/coupons') ?></div><?php endif; ?>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function post(url){ const fd=new FormData(); fd.append('_csrf_token',CSRF_TOKEN); return fetch(url,{method:'POST',body:fd}).then(r=>r.json()); }
function toggleCoupon(id){ post('<?= $p ?>/coupons/'+id+'/toggle').then(d=>{ if(!d.success) location.reload(); }); }
function deleteCoupon(id){ if(!confirm('Delete this coupon?')) return; post('<?= $p ?>/coupons/'+id+'/delete').then(d=>{ if(d.success) location.reload(); }); }
</script>
