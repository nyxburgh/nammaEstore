<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3"><div><h5 style="font-weight:800;margin:0;">Activity Logs</h5><small class="text-muted"><?= number_format($logs['total']) ?> entries</small></div></div>
<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-3"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name, action..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-2"><select name="module" class="form-select"><option value="">All Modules</option><?php foreach(['auth','vendors','customers','products','orders','banners','reports','activity','admin_roles'] as $m): ?><option value="<?= $m ?>" <?= ($filters['module']??'')===$m?'selected':'' ?>><?= ucfirst($m) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><select name="actor_type" class="form-select"><option value="">All Types</option><option value="admin" <?= ($filters['actor_type']??'')==='admin'?'selected':'' ?>>Super Admin</option><option value="sub_admin" <?= ($filters['actor_type']??'')==='sub_admin'?'selected':'' ?>>Sub Admin</option><option value="vendor" <?= ($filters['actor_type']??'')==='vendor'?'selected':'' ?>>Vendor</option></select></div>
  <div class="col-md-2"><input type="date" name="date" class="form-control" value="<?= e($filters['date']??'') ?>"></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="<?= $p ?>/activity" class="btn btn-outline-secondary">Reset</a></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/activity') ?></th><th><?= sortHeader('Actor','actor_name',$p.'/activity') ?></th><th>Type</th><th><?= sortHeader('Action','action',$p.'/activity') ?></th><th><?= sortHeader('Module','module',$p.'/activity') ?></th><th>Description</th><th>IP</th><th><?= sortHeader('Time','created_at',$p.'/activity') ?></th></tr></thead>
  <tbody>
  <?php if(empty($logs['data'])): ?><tr><td colspan="8"><div class="empty-state"><i class="bi bi-clock-history"></i><h6>No activity logs</h6></div></td></tr>
  <?php else: $tca=['admin'=>'primary','sub_admin'=>'info','vendor'=>'success','customer'=>'secondary']; foreach($logs['data'] as $log): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;">#<?= $log['id'] ?></td>
    <td><div class="d-flex align-items-center gap-2"><div class="av av-sm"><?= strtoupper(substr($log['actor_name'],0,1)) ?></div><span style="font-size:13px;font-weight:600;"><?= e($log['actor_name']) ?></span></div></td>
    <td><span class="badge badge-<?= $tca[$log['actor_type']]??'secondary' ?>"><?= ucfirst(str_replace('_',' ',$log['actor_type'])) ?></span></td>
    <td style="font-size:13px;font-weight:600;color:var(--purple);"><?= e(str_replace('_',' ',$log['action'])) ?></td>
    <td><span style="font-size:11.5px;background:var(--bg);padding:2px 8px;border-radius:6px;font-weight:600;color:var(--muted);"><?= e($log['module']) ?></span></td>
    <td style="font-size:12.5px;color:var(--muted);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($log['description']) ?></td>
    <td style="font-size:12px;color:var(--muted);"><?= e($log['ip_address']) ?></td>
    <td style="font-size:11.5px;color:var(--muted);white-space:nowrap;"><?= timeAgo($log['created_at']) ?></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($logs['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($logs,$p.'/activity') ?></div><?php endif; ?>
</div>
