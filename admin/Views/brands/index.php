<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Brands</h5><small class="text-muted"><?= number_format($brands['total']) ?> total</small></div>
  <a href="<?= $p ?>/brands/create" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Add Brand</a>
</div>

<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search brand name..." value="<?= e($filters['search']??'') ?>"></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Filter</button></div>
</form></div>

<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/brands') ?></th><th>Logo</th><th><?= sortHeader('Name','name',$p.'/brands') ?></th><th>Products</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php if(empty($brands['data'])): ?>
    <tr><td colspan="6"><div class="empty-state"><i class="bi bi-award"></i><h6>No brands yet</h6></div></td></tr>
  <?php else: foreach($brands['data'] as $b): ?>
  <tr>
    <td>#<?= $b['id'] ?></td>
    <td><?php if($b['logo']): ?><img src="<?= UPLOAD_URL.'/'.$b['logo'] ?>" alt="<?= e($b['name']) ?>" style="width:36px;height:36px;object-fit:contain;border-radius:6px;"><?php else: ?><div style="width:36px;height:36px;background:#f4f0f6;border-radius:6px;display:flex;align-items:center;justify-content:center;">🏷️</div><?php endif; ?></td>
    <td style="font-weight:600;"><?= e($b['name']) ?></td>
    <td><?= (int)$b['product_count'] ?></td>
    <td>
      <label class="form-check form-switch mb-0">
        <input class="form-check-input" type="checkbox" <?= $b['is_active']?'checked':'' ?> onchange="toggleBrand(<?= $b['id'] ?>)">
      </label>
    </td>
    <td>
      <a href="<?= $p ?>/brands/<?= $b['id'] ?>/edit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
      <button class="btn btn-sm btn-outline-danger" onclick="deleteBrand(<?= $b['id'] ?>)"><i class="bi bi-trash"></i></button>
    </td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($brands['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($brands,$p.'/brands') ?></div><?php endif; ?>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';
function post(url){ const fd=new FormData(); fd.append('_csrf_token',CSRF_TOKEN); return fetch(url,{method:'POST',body:fd}).then(r=>r.json()); }
function toggleBrand(id){ post('<?= $p ?>/brands/'+id+'/toggle').then(d=>{ if(!d.success) location.reload(); }); }
function deleteBrand(id){ if(!confirm('Delete this brand?')) return; post('<?= $p ?>/brands/'+id+'/delete').then(d=>{ if(d.success) location.reload(); }); }
</script>
