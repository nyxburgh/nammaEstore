<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Sellers</h5><small class="text-muted"><?= number_format($sellers['total']) ?> total</small></div>
  <?php if(\App\Core\Auth::isSuperAdmin()): ?><a href="<?= $p ?>/sellers/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Seller</a><?php endif; ?>
</div>
<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <?= csrf_field() ?>
  <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name, email, shop..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-2"><select name="status" class="form-select"><option value="">All Status</option><?php foreach(['active','pending','suspended','rejected'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><select name="is_active" class="form-select"><option value="">Account</option><option value="1" <?= ($filters['is_active']??'')==='1'?'selected':'' ?>>Active</option><option value="0" <?= ($filters['is_active']??'')==='0'?'selected':'' ?>>Inactive</option></select></div>
  <div class="col-md-4 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="<?= $p ?>/sellers" class="btn btn-outline-secondary">Reset</a></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/sellers') ?></th><th><?= sortHeader('Seller','name',$p.'/sellers') ?></th><th>Shop</th><th>Status</th><th>Account</th><th><?= sortHeader('Joined','created_at',$p.'/sellers') ?></th><th>Actions</th></tr></thead>
  <tbody>
  <?php if(empty($sellers['data'])): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-shop"></i><h6>No sellers found</h6></div></td></tr>
  <?php else: foreach($sellers['data'] as $v): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;"><?= $v['id'] ?></td>
    <td><div class="d-flex align-items-center gap-2"><div class="av av-sm"><?= strtoupper(substr($v['name'],0,1)) ?></div><div><div style="font-weight:600;"><?= e($v['name']) ?></div><div style="font-size:11.5px;color:var(--muted);"><?= e($v['email']) ?></div></div></div></td>
    <td style="font-weight:500;"><?= e($v['shop_name']??'—') ?></td>
    <td><?= statusBadge($v['seller_status']??'pending') ?></td>
    <td><?php if(\App\Core\Auth::isSuperAdmin()): ?><div class="form-check form-switch mb-0"><input class="form-check-input toggle-status" type="checkbox" <?= $v['is_active']?'checked':'' ?> data-url="<?= $p ?>/sellers/<?= $v['id'] ?>/toggle"></div><?php else: echo statusBadge($v['is_active']?'active':'inactive'); endif; ?></td>
    <td style="font-size:12px;color:var(--muted);"><?= formatDate($v['created_at']) ?></td>
    <td><div class="d-flex gap-1">
      <a href="<?= $p ?>/sellers/<?= $v['id'] ?>" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-eye"></i></a>
      <?php if(\App\Core\Auth::isSuperAdmin()): ?><a href="<?= $p ?>/sellers/<?= $v['id'] ?>/edit" class="btn btn-sm btn-icon btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
      <?php if(\App\Core\Auth::isSuperAdmin()&&($v['seller_status']??'')==='pending'): ?>
      <form method="post" action="<?= $p ?>/sellers/<?= $v['id'] ?>/approve" style="display:inline;">
  <?= csrf_field() ?><button class="btn btn-sm btn-icon btn-success"><i class="bi bi-check-lg"></i></button></form>
      <?php endif; ?>
    </div></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($sellers['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($sellers,$p.'/sellers') ?></div><?php endif; ?>
</div>
