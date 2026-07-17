<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3"><div><h5 style="font-weight:800;margin:0;">Products</h5><small class="text-muted"><?= number_format($products['total']) ?> total</small></div></div>
<div class="row g-2 mb-3">
  <?php foreach([['Total',$stats['total'],'purple','box-seam'],['Active',$stats['active'],'green','check-circle'],['Draft',$stats['draft'],'orange','pencil'],['Inactive',$stats['inactive'],'blue','dash-circle'],['Low Stock',$stats['low_stock'],'red','exclamation-triangle']] as [$l,$v,$c,$i]): ?>
  <div class="col"><div class="card" style="padding:10px 14px;display:flex;flex-direction:row;align-items:center;gap:10px;"><div class="si <?= $c ?>" style="width:34px;height:34px;font-size:13px;border-radius:9px;flex-shrink:0;"><i class="bi bi-<?= $i ?>"></i></div><div><div class="stat-label" style="font-size:10.5px;"><?= $l ?></div><div class="stat-val" style="font-size:17px;"><?= number_format($v) ?></div></div></div></div>
  <?php endforeach; ?>
</div>
<div class="filter-bar mb-3"><form method="get" class="row g-2 align-items-end">
  <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Name or SKU..." value="<?= e($filters['search']??'') ?>"></div></div>
  <div class="col-md-2"><select name="status" class="form-select"><option value="">All Status</option><?php foreach(['active','draft','inactive','rejected'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select name="category_id" class="form-select"><option value="">All Categories</option><?php foreach($categories as $cat): ?><option value="<?= $cat['id'] ?>" <?= ($filters['category_id']??'')==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-primary flex-fill">Filter</button><a href="<?= $p ?>/products" class="btn btn-outline-secondary">Reset</a></div>
</form></div>
<div class="card"><div class="table-responsive"><table class="table mb-0">
  <thead><tr><th><?= sortHeader('#','id',$p.'/products') ?></th><th><?= sortHeader('Product','name',$p.'/products') ?></th><th>Vendor</th><th>Category</th><th><?= sortHeader('Price','price',$p.'/products') ?></th><th><?= sortHeader('Stock','stock',$p.'/products') ?></th><th>Status</th><th>Actions</th></tr></thead>
  <tbody>
  <?php if(empty($products['data'])): ?><tr><td colspan="8"><div class="empty-state"><i class="bi bi-box-seam"></i><h6>No products</h6></div></td></tr>
  <?php else: foreach($products['data'] as $pr): ?>
  <tr>
    <td style="color:var(--muted);font-size:12px;"><?= $pr['id'] ?></td>
    <td><div class="d-flex align-items-center gap-2">
      <?php if($pr['primary_image']): ?><img src="<?= UPLOAD_URL.'/'.$pr['primary_image'] ?>" style="width:36px;height:36px;object-fit:cover;border-radius:7px;border:1px solid var(--border);"><?php else: ?><div style="width:36px;height:36px;background:var(--bg);border-radius:7px;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--muted);"><i class="bi bi-image"></i></div><?php endif; ?>
      <div><div style="font-weight:600;max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($pr['name']) ?></div><div style="font-size:11px;color:var(--muted);">SKU: <?= e($pr['sku']??'—') ?></div></div>
    </div></td>
    <td style="font-size:13px;"><?= e($pr['shop_name']??$pr['vendor_name']) ?></td>
    <td style="font-size:13px;"><?= e($pr['category_name']??'—') ?></td>
    <td style="font-weight:700;"><?= currency($pr['sale_price']??$pr['price']) ?><?php if($pr['sale_price']&&$pr['sale_price']<$pr['price']): ?><div style="font-size:11px;color:var(--muted);text-decoration:line-through;"><?= currency($pr['price']) ?></div><?php endif; ?></td>
    <td style="font-weight:600;<?= $pr['stock']<=5?'color:#dc2626;':'' ?>"><?= number_format($pr['stock']) ?><?php if($pr['stock']<=$pr['low_stock_threshold']): ?><span class="badge badge-danger ms-1" style="font-size:9px;">Low</span><?php endif; ?></td>
    <td><?php if(\App\Core\Auth::can('products','edit')): ?><select class="form-select form-select-sm prod-status" style="width:105px;font-size:12px;" data-id="<?= $pr['id'] ?>" data-url="<?= $p ?>/products/<?= $pr['id'] ?>/status"><?php foreach(['active','draft','inactive'] as $s): ?><option value="<?= $s ?>" <?= $pr['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select><?php else: echo statusBadge($pr['status']); endif; ?></td>
    <td><div class="d-flex gap-1">
      <a href="<?= $p ?>/products/<?= $pr['id'] ?>" class="btn btn-sm btn-icon btn-outline-primary"><i class="bi bi-eye"></i></a>
      <?php if(\App\Core\Auth::can('products','edit')): ?><a href="<?= $p ?>/products/<?= $pr['id'] ?>/edit" class="btn btn-sm btn-icon btn-outline-secondary"><i class="bi bi-pencil"></i></a><?php endif; ?>
      <?php if(\App\Core\Auth::can('products','delete')): ?>
      <form method="post" action="<?= $p ?>/products/<?= $pr['id'] ?>/delete" style="display:inline;" onsubmit="return confirm('Delete this product?');">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"><i class="bi bi-trash"></i></button>
      </form>
      <?php endif; ?>
    </div></td>
  </tr>
  <?php endforeach; endif; ?>
  </tbody>
</table></div>
<?php if($products['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($products,$p.'/products') ?></div><?php endif; ?>
</div>
<?php $scripts='<script>document.querySelectorAll(".prod-status").forEach(s=>{s.addEventListener("change",function(){fetch(this.dataset.url,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded","X-Requested-With":"XMLHttpRequest"},body:"status="+this.value}).then(r=>r.json()).then(d=>{if(d.success){const r=this.closest("tr");r.style.background="rgba(109,40,217,.05)";setTimeout(()=>r.style.background="",700);}});});});</script>'; ?>
