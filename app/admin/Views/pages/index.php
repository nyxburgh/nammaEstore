<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3"><div><h5 style="font-weight:800;margin:0;">Info Pages</h5><small class="text-muted"><?= number_format($pages['total']) ?> pages — shown in the storefront Info Center &amp; footer</small></div><?php if(\App\Core\Auth::can('pages','create')): ?><a href="<?= $p ?>/pages/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Page</a><?php endif; ?></div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Title or slug..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-3"><select name="sort" class="form-select" onchange="this.form.submit()">
    <option value="sort_order" <?= ($filters['sort']??'sort_order')==='sort_order'?'selected':'' ?>>Sort by: Display Order</option>
    <option value="title" <?= ($filters['sort']??'')==='title'?'selected':'' ?>>Sort by: Title</option>
    <option value="updated_at" <?= ($filters['sort']??'')==='updated_at'?'selected':'' ?>>Sort by: Last Updated</option>
  </select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="<?= $p ?>/pages" class="btn btn-outline-secondary">Reset</a></div>
</form></div>

<?php if(empty($pages['data'])): ?><div class="card"><div class="card-body"><div class="empty-state"><i class="bi bi-file-earmark-text"></i><h6>No pages found</h6><p class="text-muted mb-0">Run <code>php scripts/seed_info_pages.php</code> to seed the default Info Center pages.</p></div></div></div>
<?php else: ?>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th style="width:44px;"></th><th>Title</th><th>Slug</th><th>Order</th><th>Updated</th><th style="width:90px;">Active</th><th style="width:130px;"></th></tr></thead>
  <tbody>
  <?php foreach($pages['data'] as $pg): ?>
    <tr>
      <td style="font-size:1.2rem;"><?= $pg['icon'] ?></td>
      <td><div style="font-weight:700;"><?= e($pg['title']) ?></div><small class="text-muted"><?= e($pg['short_description']??'') ?></small></td>
      <td><a href="<?= APP_URL ?>/info/<?= e($pg['slug']) ?>" target="_blank" rel="noopener"><code>/info/<?= e($pg['slug']) ?></code> <i class="bi bi-box-arrow-up-right" style="font-size:11px;"></i></a></td>
      <td><?= $pg['sort_order'] ?></td>
      <td><small><?= formatDate($pg['updated_at']) ?></small></td>
      <td><?php if(\App\Core\Auth::can('pages','edit')): ?><div class="form-check form-switch mb-0"><input class="form-check-input toggle-status" type="checkbox" <?= $pg['is_active']?'checked':'' ?> data-url="<?= $p ?>/pages/<?= $pg['id'] ?>/toggle"></div><?php else: ?><?= $pg['is_active']?'Yes':'No' ?><?php endif; ?></td>
      <td class="text-end">
        <?php if(\App\Core\Auth::can('pages','edit')): ?><a href="<?= $p ?>/pages/<?= $pg['id'] ?>/edit" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
        <?php if(\App\Core\Auth::can('pages','delete')): ?><button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="deletePage(<?= $pg['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div></div>
<?php endif; ?>
<?php if($pages['total_pages']>1): ?><div class="mt-3"><?= paginate($pages,$p.'/pages') ?></div><?php endif; ?>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function deletePage(id){
  if(!confirm('Delete this page? The storefront link will stop working.')) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN);
  fetch('<?= $p ?>/pages/'+id+'/delete', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
</script>
