<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Customers</h5><small class="text-muted"><?= number_format($customers['total']) ?> total</small></div>
</div>
<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-5"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name, email, phone..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-3"><select name="is_active" class="form-select"><option value="">All</option><option value="1" <?= ($filters['is_active']??'')==='1'?'selected':'' ?>>Active</option><option value="0" <?= ($filters['is_active']??'')==='0'?'selected':'' ?>>Inactive</option></select></div>
  <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="<?= $p ?>/customers" class="btn btn-outline-secondary">Reset</a></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/customers') ?></th><th><?= sortHeader('Customer','name',$p.'/customers') ?></th><th>Phone</th><th>Verified</th><th>Status</th><th><?= sortHeader('Joined','created_at',$p.'/customers') ?></th><th>Actions</th></tr></thead>
  <tbody>
  <?php if(empty($customers['data'])): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i><h6>No customers</h6></div></td></tr>
  <?php else: foreach($customers['data'] as $c): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;"><?= $c['id'] ?></td>
    <td><div class="d-flex align-items-center gap-2"><div class="av av-sm" style="background:linear-gradient(135deg,#0ea5e9,#6d28d9);"><?= strtoupper(substr($c['name'],0,1)) ?></div><div><div style="font-weight:600;"><?= e($c['name']) ?></div><div style="font-size:11.5px;color:var(--muted);"><?= e($c['email']) ?></div></div></div></td>
    <td style="font-size:13px;"><?= e($c['phone']??'—') ?></td>
    <td><?= $c['is_verified']?'<span class="badge badge-success">Yes</span>':'<span class="badge badge-secondary">No</span>' ?></td>
    <td><?php if(\App\Core\Auth::isSuperAdmin()): ?><div class="form-check form-switch mb-0"><input class="form-check-input toggle-status" type="checkbox" <?= $c['is_active']?'checked':'' ?> data-url="<?= $p ?>/customers/<?= $c['id'] ?>/toggle"></div><?php else: echo statusBadge($c['is_active']?'active':'inactive'); endif; ?></td>
    <td style="font-size:12px;color:var(--muted);"><?= formatDate($c['created_at']) ?></td>
    <td><div class="d-flex gap-1">
      <a href="<?= $p ?>/customers/<?= $c['id'] ?>" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-eye"></i></a>
      <?php if(\App\Core\Auth::isSuperAdmin()): ?><a href="<?= $p ?>/customers/<?= $c['id'] ?>/edit" class="btn btn-sm btn-icon btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
    </div></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($customers['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($customers,$p.'/customers') ?></div><?php endif; ?>
</div>
