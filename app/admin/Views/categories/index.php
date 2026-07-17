<?php $p = ADMIN_URL; ?>

<div class="d-flex align-items-center justify-content-between mb-3">
  <div>
    <h5 style="font-weight:800;margin:0;">Categories</h5>
    <small class="text-muted">
      <?= count($parents) ?> parent<?= count($parents) != 1 ? 's' : '' ?>,
      <?= array_sum(array_map('count', $children)) ?> subcategor<?= array_sum(array_map('count', $children)) != 1 ? 'ies' : 'y' ?>
    </small>
  </div>
  <?php if(\App\Core\Auth::can('products','edit')): ?>
  <a href="<?= $p ?>/categories/create" class="btn btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Add Category
  </a>
  <?php endif; ?>
</div>

<?php if(empty($parents)): ?>
<div class="card">
  <div class="empty-state" style="padding:60px;">
    <i class="bi bi-tags" style="font-size:36px;color:var(--muted);"></i>
    <h6 class="mt-3">No categories yet</h6>
    <p class="text-muted" style="font-size:13px;">Add your first category to start organising products</p>
    <a href="<?= $p ?>/categories/create" class="btn btn-primary btn-sm mt-2">Add Category</a>
  </div>
</div>

<?php else: foreach($parents as $parent):
  $subs = $children[$parent['id']] ?? [];
?>

<!-- ── PARENT CATEGORY CARD ────────────────────────────────── -->
<div class="card mb-3" id="cat-card-<?= $parent['id'] ?>">

  <!-- Parent row -->
  <div class="d-flex align-items-center px-4 py-3 gap-3" style="border-bottom:<?= empty($subs) ? 'none' : '1px solid var(--border)' ?>;">
    <div class="flex-fill" style="min-width:0;">
      <div style="font-weight:700;font-size:14px;"><?= e($parent['name']) ?></div>
      <div style="font-size:11px;color:var(--muted);">
        <code style="background:var(--bg);padding:1px 6px;border-radius:4px;"><?= e($parent['slug']) ?></code>
        <?php if($parent['description']): ?>
          &nbsp;·&nbsp;<span><?= e(substr($parent['description'],0,60)) ?><?= strlen($parent['description'])>60?'…':'' ?></span>
        <?php endif; ?>
      </div>
    </div>

    <!-- Subcategory count badge -->
    <div class="text-center" style="min-width:70px;">
      <?php if(!empty($subs)): ?>
      <button class="btn btn-sm btn-outline-secondary" style="font-size:11px;padding:3px 10px;" onclick="toggleSubs(<?= $parent['id'] ?>)">
        <i class="bi bi-diagram-2 me-1"></i><?= count($subs) ?> sub<?= count($subs)!=1?'s':'' ?>
      </button>
      <?php else: ?>
      <span style="font-size:12px;color:var(--muted);">No subs</span>
      <?php endif; ?>
    </div>

    <!-- Product count -->
    <div class="text-center" style="min-width:60px;">
      <span class="badge badge-secondary"><?= number_format($parent['product_count']) ?> products</span>
    </div>

    <!-- Sort order -->
    <div style="min-width:40px;text-align:center;font-size:12px;color:var(--muted);">
      #<?= $parent['sort_order'] ?>
    </div>

    <!-- Status -->
    <div style="min-width:70px;">
      <span class="badge <?= $parent['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
        <?= $parent['is_active'] ? 'Active' : 'Inactive' ?>
      </span>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-1">
      <?php if(\App\Core\Auth::can('products','edit')): ?>
      <a href="<?= $p ?>/categories/<?= $parent['id'] ?>/edit" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
      <button class="btn btn-sm btn-icon btn-outline-<?= $parent['is_active']?'warning':'success' ?>"
              onclick="toggleCat(<?= $parent['id'] ?>)" title="<?= $parent['is_active']?'Deactivate':'Activate' ?>">
        <i class="bi bi-<?= $parent['is_active']?'pause':'play' ?>-fill"></i>
      </button>
      <?php endif; ?>
      <?php if(\App\Core\Auth::can('products','delete')): ?>
      <button class="btn btn-sm btn-icon btn-outline-danger"
              onclick="deleteCat(<?= $parent['id'] ?>, <?= $parent['product_count'] ?>, <?= count($subs) ?>)" title="Delete">
        <i class="bi bi-trash"></i>
      </button>
      <?php endif; ?>
      <?php if(\App\Core\Auth::can('products','edit')): ?>
      <a href="<?= $p ?>/categories/create?parent_id=<?= $parent['id'] ?>" class="btn btn-sm btn-icon btn-outline-success" title="Add subcategory">
        <i class="bi bi-plus-lg"></i>
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Subcategory rows (collapsible) -->
  <?php if(!empty($subs)): ?>
  <div id="subs-<?= $parent['id'] ?>" class="sub-list">
    <?php foreach($subs as $i => $sub): ?>
    <div class="d-flex align-items-center px-4 py-2 gap-3 sub-row" id="cat-card-<?= $sub['id'] ?>"
         style="background:#fafafa;border-bottom:<?= $i < count($subs)-1 ? '1px solid var(--border)' : 'none' ?>;">

      <!-- Indent + name -->
      <div class="flex-fill" style="min-width:0;padding-left:22px;">
        <div style="display:flex;align-items:center;gap:6px;">
          <span style="color:var(--muted);font-size:13px;">↳</span>
          <span style="font-weight:600;font-size:13px;"><?= e($sub['name']) ?></span>
        </div>
        <div style="font-size:11px;color:var(--muted);padding-left:18px;">
          <code style="background:var(--bg);padding:1px 6px;border-radius:4px;"><?= e($sub['slug']) ?></code>
        </div>
      </div>

      <div style="min-width:70px;"></div><!-- align with parent's subs column -->

      <!-- Product count -->
      <div class="text-center" style="min-width:60px;">
        <span class="badge badge-secondary"><?= number_format($sub['product_count']) ?> products</span>
      </div>

      <!-- Sort -->
      <div style="min-width:40px;text-align:center;font-size:12px;color:var(--muted);">#<?= $sub['sort_order'] ?></div>

      <!-- Status -->
      <div style="min-width:70px;">
        <span class="badge <?= $sub['is_active'] ? 'badge-success' : 'badge-secondary' ?>">
          <?= $sub['is_active'] ? 'Active' : 'Inactive' ?>
        </span>
      </div>

      <!-- Actions -->
      <div class="d-flex gap-1">
        <?php if(\App\Core\Auth::can('products','edit')): ?>
        <a href="<?= $p ?>/categories/<?= $sub['id'] ?>/edit" class="btn btn-sm btn-icon btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
        <button class="btn btn-sm btn-icon btn-outline-<?= $sub['is_active']?'warning':'success' ?>"
                onclick="toggleCat(<?= $sub['id'] ?>)" title="<?= $sub['is_active']?'Deactivate':'Activate' ?>">
          <i class="bi bi-<?= $sub['is_active']?'pause':'play' ?>-fill"></i>
        </button>
        <?php endif; ?>
        <?php if(\App\Core\Auth::can('products','delete')): ?>
        <button class="btn btn-sm btn-icon btn-outline-danger"
                onclick="deleteCat(<?= $sub['id'] ?>, <?= $sub['product_count'] ?>, 0)" title="Delete">
          <i class="bi bi-trash"></i>
        </button>
        <?php endif; ?>
        <div style="width:30px;"></div><!-- align with parent's + button -->
      </div>

    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php endforeach; endif; ?>

<?php $scripts = <<<'JS'
<script>
function toggleSubs(id) {
  const el = document.getElementById('subs-'+id);
  if (!el) return;
  el.style.display = el.style.display === 'none' ? '' : 'none';
}

function toggleCat(id) {
  fetch(ADMIN_URL+'/categories/'+id+'/toggle', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  }).then(r => r.json()).then(d => {
    if (d.success) location.reload();
  });
}

function deleteCat(id, productCount, subCount) {
  if (subCount > 0) {
    alert('Cannot delete: this category has ' + subCount + ' subcategory/ies. Delete or reassign them first.');
    return;
  }
  if (productCount > 0) {
    alert('Cannot delete: ' + productCount + ' product(s) use this category. Reassign them first.');
    return;
  }
  if (!confirm('Delete this category permanently?')) return;
  fetch(ADMIN_URL+'/categories/'+id+'/delete', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: '_csrf_token=' + CSRF_TOKEN
  }).then(r => r.json()).then(d => {
    if (d.success) {
      const el = document.getElementById('cat-card-'+id);
      if (el) el.closest('.card, .sub-row').remove();
    } else {
      alert(d.message || 'Could not delete.');
    }
  });
}
</script>
JS; ?>
