<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 style="font-weight:800;margin:0;">Issue Gift Voucher</h5>
  <a href="<?= $p ?>/gift-cards" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<form method="POST" action="<?= $p ?>/gift-cards/create" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="card"><div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Type *</label>
        <select name="type" id="typeSelect" class="form-select" required oninput="validateField(this)" onblur="validateField(this)">
          <option value="company" <?= ($old['type']??'')==='company'?'selected':'' ?>>Company Gift Card</option>
          <option value="seller" <?= ($old['type']??'')==='seller'?'selected':'' ?>>Seller Gift Card</option>
          <option value="recharge" <?= ($old['type']??'')==='recharge'?'selected':'' ?>>Recharge Gift Card</option>
        </select>
      </div>
      <div class="col-md-4" id="sellerWrap" style="display:none;">
        <label class="form-label">Seller *</label>
        <select name="seller_id" class="form-select">
          <option value="">— select seller —</option>
          <?php foreach(($sellers ?? []) as $v): ?>
          <option value="<?= $v['id'] ?>" <?= ($old['seller_id']??'')==$v['id']?'selected':'' ?>><?= e($v['shop_name'] ?: $v['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Amount (₹) *</label>
        <input type="number" step="0.01" min="0" name="amount" class="form-control" required value="<?= e($old['amount']??'') ?>" oninput="validateField(this)" onblur="validateField(this)">
      </div>
      <div class="col-md-6">
        <label class="form-label">Issue to (email, optional)</label>
        <input type="email" name="issued_to_email" class="form-control" placeholder="customer@example.com" value="<?= e($old['issued_to_email']??'') ?>" oninput="validateField(this)" onblur="validateField(this)">
      </div>
      <div class="col-md-6">
        <label class="form-label">Expires</label>
        <input type="datetime-local" name="expires_at" class="form-control" value="<?= e($old['expires_at']??'') ?>" oninput="validateField(this)" onblur="validateField(this)">
      </div>
    </div>
  </div></div>
  <div class="mt-3">
    <button type="submit" class="btn btn-primary">Issue Voucher</button>
    <a href="<?= $p ?>/gift-cards" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
<?php $scripts = '<script src="'.admin_asset('js/gift-cards-form.js').'"></script>'; ?>
