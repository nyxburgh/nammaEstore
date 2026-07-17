<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3"><div><h5 style="font-weight:800;margin:0;">Sub-Admins</h5><small class="text-muted">Module-based access control</small></div><a href="<?= $p ?>/sub-admins/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Sub-Admin</a></div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name or email..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/sub-admins') ?></th><th><?= sortHeader('Admin','name',$p.'/sub-admins') ?></th><th>Modules</th><th>Status</th><th><?= sortHeader('Last Login','last_login',$p.'/sub-admins') ?></th><th><?= sortHeader('Created','created_at',$p.'/sub-admins') ?></th><th>Actions</th></tr></thead>
  <tbody>
  <?php if(empty($subAdmins['data'])): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-person-badge"></i><h6>No sub-admins found</h6></div></td></tr>
  <?php else: foreach($subAdmins['data'] as $sa): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;">#<?= $sa['id'] ?></td>
    <td><div class="d-flex align-items-center gap-2"><div class="av av-sm" style="background:linear-gradient(135deg,#0ea5e9,#6366f1);"><?= strtoupper(substr($sa['name'],0,1)) ?></div><div><div style="font-weight:600;"><?= e($sa['name']) ?></div><div style="font-size:11.5px;color:var(--muted);"><?= e($sa['email']) ?></div></div></div></td>
    <td><span style="font-size:13px;font-weight:600;color:var(--purple);"><?= (int)$sa['perm_count'] ?> module<?= $sa['perm_count']!=1?'s':'' ?></span></td>
    <td><div class="form-check form-switch mb-0"><input class="form-check-input toggle-status" type="checkbox" <?= $sa['is_active']?'checked':'' ?> data-url="<?= $p ?>/sub-admins/<?= $sa['id'] ?>/toggle"></div></td>
    <td style="font-size:12px;color:var(--muted);"><?= $sa['last_login']?timeAgo($sa['last_login']):'Never' ?></td>
    <td style="font-size:12px;color:var(--muted);"><?= formatDate($sa['created_at']) ?></td>
    <td><div class="d-flex gap-1">
      <a href="<?= $p ?>/sub-admins/<?= $sa['id'] ?>/edit" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-pencil"></i></a>
      <button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="deleteSubAdmin(<?= $sa['id'] ?>,'<?= e($sa['name']) ?>')"><i class="bi bi-trash"></i></button>
    </div></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($subAdmins['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($subAdmins,$p.'/sub-admins') ?></div><?php endif; ?>
</div>
<div class="alert alert-info mt-3"><i class="bi bi-info-circle-fill me-2"></i><strong>Restrictions:</strong> Sub-admins cannot change passwords, access system settings, or manage admin roles. Access is strictly limited to assigned modules.</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function deleteSubAdmin(id, name){
  if(!confirm('Delete '+name+'?')) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN);
  fetch('<?= $p ?>/sub-admins/'+id+'/delete', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message||'Delete failed.'); });
}
</script>
