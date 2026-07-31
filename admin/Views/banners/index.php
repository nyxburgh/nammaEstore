<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3"><div><h5 style="font-weight:800;margin:0;">Banners</h5><small class="text-muted"><?= number_format($banners['total']) ?> banners</small></div><?php if(\App\Core\Auth::can('banners','create')): ?><a href="<?= $p ?>/banners/create" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Add Banner</a><?php endif; ?></div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Title..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-3"><select name="sort" class="form-select" onchange="this.form.submit()">
    <option value="sort_order" <?= ($filters['sort']??'sort_order')==='sort_order'?'selected':'' ?>>Sort by: Display Order</option>
    <option value="title" <?= ($filters['sort']??'')==='title'?'selected':'' ?>>Sort by: Title</option>
    <option value="created_at" <?= ($filters['sort']??'')==='created_at'?'selected':'' ?>>Sort by: Date Added</option>
    <option value="id" <?= ($filters['sort']??'')==='id'?'selected':'' ?>>Sort by: ID</option>
  </select></div>
  <input type="hidden" name="position" value="<?= e($filters['position']??'') ?>">
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="<?= $p ?>/banners" class="btn btn-outline-secondary">Reset</a></div>
</form></div>

<div class="d-flex gap-2 mb-3 flex-wrap">
  <?php foreach([''=> 'All','hero'=>'Hero','sidebar'=>'Sidebar','popup'=>'Popup','top_bar'=>'Top Bar'] as $pos=>$lbl): ?>
  <a href="<?= $p ?>/banners?position=<?= $pos ?>" class="btn btn-sm <?= ($filters['position']??'')===$pos?'btn-primary':'btn-outline-secondary' ?>" style="border-radius:20px;"><?= $lbl ?></a>
  <?php endforeach; ?>
</div>
<?php if(empty($banners['data'])): ?><div class="card"><div class="card-body"><div class="empty-state"><i class="bi bi-images"></i><h6>No banners found</h6></div></div></div>
<?php else: ?><div class="row g-3">
  <?php foreach($banners['data'] as $b): ?>
  <div class="col-lg-4 col-md-6"><div class="card" style="overflow:hidden;">
    <div style="position:relative;"><img src="<?= UPLOAD_URL.'/'.$b['image_path'] ?>" style="width:100%;height:155px;object-fit:cover;display:block;">
      <div style="position:absolute;top:8px;left:8px;display:flex;gap:6px;">
        <span class="badge badge-primary" style="background:rgba(109,40,217,.85);color:#fff;"><?= ucfirst(str_replace('_',' ',$b['position'])) ?></span>
        <span class="badge" style="background:rgba(0,0,0,.55);color:#fff;">#<?= $b['id'] ?></span>
      </div>
      <?php if(\App\Core\Auth::can('banners','edit')): ?><div style="position:absolute;top:8px;right:8px;background:rgba(255,255,255,.9);border-radius:20px;padding:3px 8px;"><div class="form-check form-switch mb-0"><input class="form-check-input toggle-status" type="checkbox" <?= $b['is_active']?'checked':'' ?> data-url="<?= $p ?>/banners/<?= $b['id'] ?>/toggle" style="margin-top:1px;"></div></div><?php endif; ?>
    </div>
    <div class="card-body" style="padding:13px;">
      <div style="font-weight:700;font-size:14px;margin-bottom:4px;"><?= e($b['title']) ?></div>
      <div style="font-size:11.5px;color:var(--muted);margin-bottom:10px;">Sort: <?= $b['sort_order'] ?><?php if($b['starts_at']||$b['ends_at']): ?> &bull; <?= $b['starts_at']?formatDate($b['starts_at']):'∞' ?> → <?= $b['ends_at']?formatDate($b['ends_at']):'∞' ?><?php endif; ?></div>
      <div class="d-flex gap-2">
        <?php if(\App\Core\Auth::can('banners','edit')): ?><a href="<?= $p ?>/banners/<?= $b['id'] ?>/edit" class="btn btn-sm btn-outline-primary flex-fill"><i class="bi bi-pencil me-1"></i>Edit</a><?php endif; ?>
        <?php if(\App\Core\Auth::can('banners','delete')): ?><button type="button" class="btn btn-sm btn-icon btn-outline-danger" onclick="deleteBanner(<?= $b['id'] ?>)"><i class="bi bi-trash"></i></button><?php endif; ?>
      </div>
    </div>
  </div></div>
  <?php endforeach; ?>
</div><?php endif; ?>
<?php if($banners['total_pages']>1): ?><div class="mt-3"><?= paginate($banners,$p.'/banners') ?></div><?php endif; ?>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function deleteBanner(id){
  if(!confirm('Delete this banner?')) return;
  const fd = new FormData(); fd.append('_csrf_token', CSRF_TOKEN);
  fetch('<?= $p ?>/banners/'+id+'/delete', {method:'POST', body:fd}).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
</script>
