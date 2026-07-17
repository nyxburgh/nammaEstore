<?php $p=ADMIN_URL; $c=$coupon ?? []; $isEdit=!empty($c); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 style="font-weight:800;margin:0;"><?= $isEdit?'Edit':'Create' ?> Coupon</h5>
  <a href="<?= $p ?>/coupons" class="btn btn-outline-secondary btn-sm">← Back to Coupons</a>
</div>

<form method="POST" action="<?= $isEdit ? $p.'/coupons/'.$c['id'].'/edit' : $p.'/coupons/create' ?>">
  <?= csrf_field() ?>
  <div class="card"><div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Coupon Code *</label>
        <input type="text" name="code" class="form-control text-uppercase" value="<?= e($c['code'] ?? '') ?>" <?= $isEdit?'readonly':'required' ?> placeholder="SAVE20">
      </div>
      <div class="col-md-8">
        <label class="form-label">Label (internal note)</label>
        <input type="text" name="label" class="form-control" value="<?= e($c['label'] ?? '') ?>" placeholder="Diwali Sale 2026">
      </div>

      <div class="col-md-3">
        <label class="form-label">Value Type *</label>
        <select name="value_type" class="form-select" required>
          <option value="fixed" <?= ($c['value_type']??'')==='fixed'?'selected':'' ?>>Fixed Amount (₹)</option>
          <option value="percentage" <?= ($c['value_type']??'')==='percentage'?'selected':'' ?>>Percentage (%)</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Value *</label>
        <input type="number" step="0.01" name="value" class="form-control" value="<?= e($c['value'] ?? '') ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Max Discount Cap (₹)</label>
        <input type="number" step="0.01" name="max_discount_amount" class="form-control" value="<?= e($c['max_discount_amount'] ?? '') ?>" placeholder="For % coupons">
      </div>
      <div class="col-md-3">
        <label class="form-label">Min Order Amount (₹)</label>
        <input type="number" step="0.01" name="min_order_amount" class="form-control" value="<?= e($c['min_order_amount'] ?? 0) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Scope *</label>
        <select name="scope" id="scopeSelect" class="form-select" onchange="toggleScopeTarget()" required>
          <?php foreach(['platform'=>'Platform-wide','vendor'=>'Vendor-specific','category'=>'Category-specific','brand'=>'Brand-specific','product'=>'Product-specific','user'=>'Single User','first_order'=>'First Order Only','festival'=>'Festival (platform-wide)'] as $val=>$label): ?>
          <option value="<?= $val ?>" <?= ($c['scope']??'platform')===$val?'selected':'' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4" id="scopeTargetWrap">
        <label class="form-label">Target</label>
        <select name="scope_id" class="form-select">
          <option value="">— select —</option>
          <?php foreach(($vendors ?? []) as $v): ?><option value="<?= $v['id'] ?>" data-scope="vendor" <?= ($c['scope']??'')==='vendor' && ($c['scope_id']??null)==$v['id']?'selected':'' ?>><?= e($v['shop_name'] ?: $v['name']) ?></option><?php endforeach; ?>
          <?php foreach(($categories ?? []) as $cat): ?><option value="<?= $cat['id'] ?>" data-scope="category" <?= ($c['scope']??'')==='category' && ($c['scope_id']??null)==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?>
          <?php foreach(($brands ?? []) as $b): ?><option value="<?= $b['id'] ?>" data-scope="brand" <?= ($c['scope']??'')==='brand' && ($c['scope_id']??null)==$b['id']?'selected':'' ?>><?= e($b['name']) ?></option><?php endforeach; ?>
        </select>
        <small class="text-muted" id="scopeHint" style="font-size:11px;">For User scope, enter the numeric user ID directly.</small>
      </div>
      <div class="col-md-4">
        <label class="form-label">Or User ID (for Single User scope)</label>
        <input type="number" name="scope_id_user" onchange="document.querySelector('[name=scope_id]').value=this.value" class="form-control" placeholder="e.g. 42">
      </div>

      <div class="col-md-3">
        <label class="form-label">Total Usage Limit</label>
        <input type="number" name="usage_limit_total" class="form-control" value="<?= e($c['usage_limit_total'] ?? '') ?>" placeholder="Unlimited">
      </div>
      <div class="col-md-3">
        <label class="form-label">Per-User Limit</label>
        <input type="number" name="usage_limit_per_user" class="form-control" value="<?= e($c['usage_limit_per_user'] ?? 1) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Starts</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="<?= isset($c['starts_at']) ? date('Y-m-d\TH:i',strtotime($c['starts_at'])) : '' ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label">Expires</label>
        <input type="datetime-local" name="expires_at" class="form-control" value="<?= isset($c['expires_at']) ? date('Y-m-d\TH:i',strtotime($c['expires_at'])) : '' ?>">
      </div>

      <div class="col-12">
        <label class="form-check">
          <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= ($c['is_active']??1)?'checked':'' ?>>
          <span class="form-check-label">Active</span>
        </label>
      </div>
    </div>
  </div></div>

  <div class="mt-3">
    <button type="submit" class="btn btn-primary"><?= $isEdit?'Update':'Create' ?> Coupon</button>
    <a href="<?= $p ?>/coupons" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>

<script>
function toggleScopeTarget(){
  const scope = document.getElementById('scopeSelect').value;
  document.querySelectorAll('#scopeTargetWrap option[data-scope]').forEach(o=>{
    o.style.display = o.dataset.scope === scope ? '' : 'none';
  });
  document.getElementById('scopeTargetWrap').style.display =
    ['vendor','category','brand'].includes(scope) ? '' : 'none';
}
toggleScopeTarget();
</script>
