<?php $p = ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <div><h5 style="font-weight:800;margin:0;">Reviews</h5><small class="text-muted"><?= number_format($stats['total']) ?> total reviews</small></div>
</div>

<!-- Stats strip -->
<div class="row g-2 mb-3">
  <?php foreach([
    ['Pending',  $stats['pending'],  'orange', 'hourglass-split'],
    ['Approved', $stats['approved'], 'green',  'check-circle-fill'],
    ['Flagged',  $stats['flagged'],  'red',    'flag-fill'],
    ['Total',    $stats['total'],    'purple', 'star-fill'],
  ] as [$l,$v,$c,$i]): ?>
  <div class="col-md-3 col-6">
    <div class="card" style="padding:12px 16px;display:flex;flex-direction:row;align-items:center;gap:10px;">
      <div class="si <?= $c ?>" style="width:36px;height:36px;font-size:13px;border-radius:9px;flex-shrink:0;"><i class="bi bi-<?= $i ?>"></i></div>
      <div><div class="stat-label" style="font-size:10.5px;"><?= $l ?></div><div class="stat-val" style="font-size:18px;"><?= number_format($v) ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Filters -->
<div class="filter-bar mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-4"><div class="input-group"><span class="input-group-text"><i class="bi bi-search"></i></span><input type="text" name="search" class="form-control" placeholder="Product or customer name..." value="<?= e($filters['search']) ?>"></div></div>
    <div class="col-md-2">
      <select name="status" class="form-select">
        <option value="">All Status</option>
        <option value="pending"  <?= $filters['status']==='pending'?'selected':'' ?>>Pending</option>
        <option value="approved" <?= $filters['status']==='approved'?'selected':'' ?>>Approved</option>
        <option value="flagged"  <?= $filters['status']==='flagged'?'selected':'' ?>>Flagged</option>
      </select>
    </div>
    <div class="col-md-2">
      <select name="rating" class="form-select">
        <option value="">All Ratings</option>
        <?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>" <?= $filters['rating']==$i?'selected':'' ?>><?= $i ?> ★</option><?php endfor; ?>
      </select>
    </div>
    <div class="col-md-2 d-flex gap-2">
      <button class="btn btn-primary flex-fill">Filter</button>
      <a href="<?= $p ?>/reviews" class="btn btn-outline-secondary">Reset</a>
    </div>
  </form>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table mb-0">
      <thead><tr><th><?= sortHeader('#','id',$p.'/reviews') ?></th><th>Product</th><th>Customer</th><th><?= sortHeader('Rating','rating',$p.'/reviews') ?></th><th>Review</th><th>Status</th><th><?= sortHeader('Date','created_at',$p.'/reviews') ?></th><th>Actions</th></tr></thead>
      <tbody>
      <?php if(empty($reviews['data'])): ?>
        <tr><td colspan="8"><div class="empty-state"><i class="bi bi-star"></i><h6>No reviews found</h6></div></td></tr>
      <?php else: foreach($reviews['data'] as $r): ?>
      <tr id="row-<?= $r['id'] ?>">
        <td style="color:var(--muted);font-size:12px;">#<?= $r['id'] ?></td>
        <td>
          <a href="<?= $p ?>/products/<?= $r['product_id'] ?>" style="font-weight:600;color:var(--purple);text-decoration:none;font-size:13px;"><?= e($r['product_name']) ?></a>
          <?php if($r['shop_name']): ?><div style="font-size:11px;color:var(--muted);">🏪 <?= e($r['shop_name']) ?></div><?php endif; ?>
        </td>
        <td>
          <div style="font-weight:600;font-size:13px;"><?= e($r['customer_name']) ?></div>
          <div style="font-size:11px;color:var(--muted);"><?= e($r['customer_email']) ?></div>
        </td>
        <td>
          <span style="color:#f59e0b;font-size:14px;letter-spacing:1px;"><?= str_repeat('★',$r['rating']) ?><span style="color:var(--border);"><?= str_repeat('★',5-$r['rating']) ?></span></span>
          <div style="font-size:11px;color:var(--muted);"><?= $r['rating'] ?>/5</div>
        </td>
        <td style="max-width:260px;">
          <?php if($r['title']): ?><div style="font-weight:600;font-size:12.5px;margin-bottom:2px;"><?= e($r['title']) ?></div><?php endif; ?>
          <div style="font-size:12px;color:var(--muted);overflow:hidden;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;"><?= e($r['body']) ?></div>
        </td>
        <td>
          <?php if($r['is_flagged']): ?>
            <span class="badge badge-danger">Flagged</span>
          <?php elseif($r['is_approved']): ?>
            <span class="badge badge-success">Approved</span>
          <?php else: ?>
            <span class="badge badge-warning">Pending</span>
          <?php endif; ?>
        </td>
        <td style="font-size:12px;white-space:nowrap;"><?= formatDate($r['created_at']) ?></td>
        <td>
          <div class="d-flex gap-1 flex-wrap">
            <?php if(!$r['is_approved'] && !$r['is_flagged']): ?>
            <button class="btn btn-sm btn-success" style="padding:3px 10px;font-size:12px;" onclick="reviewAction(<?= $r['id'] ?>,'approve')">✓ Approve</button>
            <button class="btn btn-sm btn-warning" style="padding:3px 10px;font-size:12px;" onclick="reviewAction(<?= $r['id'] ?>,'reject')">✕ Flag</button>
            <?php elseif($r['is_approved']): ?>
            <button class="btn btn-sm btn-outline-warning" style="padding:3px 10px;font-size:12px;" onclick="reviewAction(<?= $r['id'] ?>,'reject')">Flag</button>
            <?php elseif($r['is_flagged']): ?>
            <button class="btn btn-sm btn-outline-success" style="padding:3px 10px;font-size:12px;" onclick="reviewAction(<?= $r['id'] ?>,'approve')">Approve</button>
            <?php endif; ?>
            <button class="btn btn-sm btn-icon btn-outline-danger" onclick="reviewAction(<?= $r['id'] ?>,'delete')" data-confirm="Delete this review permanently?"><i class="bi bi-trash"></i></button>
          </div>
        </td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if($reviews['total_pages']>1): ?><div class="card-body pt-2"><?= paginate($reviews, $p.'/reviews') ?></div><?php endif; ?>
</div>

<?php $scripts = <<<'JS'
<script>
function reviewAction(id, action) {
  if(action === 'delete' && !confirm('Delete this review permanently?')) return;
  fetch(ADMIN_URL+'/reviews/'+id+'/'+action, {
    method: 'POST',
    headers: {'Content-Type':'application/x-www-form-urlencoded'},
    body: '_csrf_token='+CSRF_TOKEN
  })
  .then(r=>r.json()).then(d=>{
    if(d.success) location.reload();
    else alert(d.message || 'Error');
  });
}
</script>
JS; ?>
