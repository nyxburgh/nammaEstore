<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= $p ?>/products" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-fill"><h5 style="font-weight:800;margin:0;"><?= e($product['name']) ?></h5><small class="text-muted">SKU: <?= e($product['sku']??'—') ?> &bull; <?= e($product['shop_name']??$product['seller_name']) ?></small></div>
  <?php if(\App\Core\Auth::can('products','edit')): ?>
  <div class="d-flex gap-2">
    <form method="post" action="<?= $p ?>/products/<?= $product['id'] ?>/status">
  <?= csrf_field() ?><input type="hidden" name="status" value="active"><button class="btn btn-success btn-sm" <?= $product['status']==='active'?'disabled':'' ?>>Activate</button></form>
    <form method="post" action="<?= $p ?>/products/<?= $product['id'] ?>/status">
  <?= csrf_field() ?><input type="hidden" name="status" value="inactive"><button class="btn btn-warning btn-sm" <?= $product['status']==='inactive'?'disabled':'' ?>>Deactivate</button></form>
  </div>
  <?php endif; ?>
</div>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="card mb-3"><div class="card-body">
      <?php $pri=null; foreach($product['images'] as $img){if($img['is_primary'])$pri=$img;} ?>
      <?php if($pri): ?><img src="<?= UPLOAD_URL.'/'.$pri['image_path'] ?>" style="width:100%;border-radius:10px;object-fit:cover;aspect-ratio:1/1;margin-bottom:10px;">
      <?php else: ?><div style="width:100%;aspect-ratio:1/1;background:var(--bg);border-radius:10px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:40px;margin-bottom:10px;"><i class="bi bi-image"></i></div><?php endif; ?>
      <?php if(count($product['images'])>1): ?><div class="d-flex gap-2 flex-wrap"><?php foreach($product['images'] as $img): ?><img src="<?= UPLOAD_URL.'/'.$img['image_path'] ?>" style="width:52px;height:52px;border-radius:7px;object-fit:cover;border:2px solid <?= $img['is_primary']?'var(--purple)':'var(--border)' ?>;"><?php endforeach; ?></div><?php endif; ?>
    </div></div>
    <div class="card"><div class="card-body">
      <?php foreach([['Status',statusBadge($product['status'])],['Featured',$product['is_featured']?'<span class="badge badge-warning">Yes</span>':'<span class="badge badge-secondary">No</span>'],['Views',number_format($product['views'])],['Added',formatDate($product['created_at'])]] as [$l,$v]): ?>
      <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--border);"><span style="font-size:12.5px;color:var(--muted);"><?= $l ?></span><span><?= $v ?></span></div>
      <?php endforeach; ?>
    </div></div>
  </div>
  <div class="col-lg-8">
    <div class="card mb-3"><div class="card-header"><span class="card-title">Product Details</span></div><div class="card-body"><div class="row g-3">
      <div class="col-md-6"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Price</div><div style="font-size:24px;font-weight:800;color:var(--purple);"><?= currency($product['price']) ?></div></div>
      <?php if($product['sale_price']): ?><div class="col-md-6"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Sale Price</div><div style="font-size:24px;font-weight:800;color:var(--pink);"><?= currency($product['sale_price']) ?></div></div><?php endif; ?>
      <div class="col-md-4"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Stock</div><div style="font-size:20px;font-weight:800;<?= $product['stock']<=5?'color:#dc2626;':'' ?>"><?= number_format($product['stock']) ?></div></div>
      <div class="col-md-4"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Category</div><div style="font-size:14px;font-weight:600;"><?= e($product['category_name']??'—') ?></div></div>
      <div class="col-md-4"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;">Weight</div><div style="font-size:14px;font-weight:600;"><?= $product['weight']?$product['weight'].' kg':'—' ?></div></div>
      <?php if($product['description']): ?><div class="col-12"><div style="font-size:10.5px;color:var(--muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">Description</div><div style="font-size:13.5px;color:var(--muted);line-height:1.7;"><?= nl2br(e($product['description'])) ?></div></div><?php endif; ?>
    </div></div></div>
    <?php if(!empty($product['variants'])): ?>
    <div class="card"><div class="card-header"><span class="card-title">Variants (<?= count($product['variants']) ?>)</span></div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th>Attribute</th><th>Value</th><th>Price Modifier</th><th>Stock</th></tr></thead><tbody>
    <?php foreach($product['variants'] as $v): ?><tr><td style="font-weight:600;"><?= e($v['variant_name']) ?></td><td><?= e($v['variant_value']) ?></td><td><?= $v['price_modifier']!=0?($v['price_modifier']>0?'+':'').currency($v['price_modifier']):'—' ?></td><td><?= number_format($v['stock']) ?></td></tr><?php endforeach; ?>
    </tbody></table></div></div>
    <?php endif; ?>
  </div>
</div>
