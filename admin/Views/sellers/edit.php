<?php $p=ADMIN_URL; $v=$seller; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/sellers/<?= $v['id'] ?>" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><div><h5 style="font-weight:800;margin:0;">Edit Seller: <?= e($v['shop_name'] ?? $v['name']) ?></h5></div></div>
<div class="row g-3">
  <div class="col-lg-8">
    <form method="POST" action="<?= $p ?>/sellers/<?= $v['id'] ?>/edit" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
      <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="bi bi-person me-2"></i>Account Details</span></div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= e($old['name'] ?? $v['name']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($old['email'] ?? $v['email']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($old['phone'] ?? $v['phone'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="col-md-6"><label class="form-label">Reset Password</label><input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current" oninput="validateField(this)" onblur="validateField(this)"></div>
      </div></div></div>
      <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="bi bi-shop me-2"></i>Shop Details</span></div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Shop Name *</label><input type="text" name="shop_name" class="form-control" required value="<?= e($old['shop_name'] ?? $v['shop_name'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="col-md-6"><label class="form-label">GST Number</label><input type="text" name="gst_number" class="form-control" value="<?= e($old['gst_number'] ?? $v['gst_number'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">PAN Number</label><input type="text" name="pan_number" class="form-control" value="<?= e($old['pan_number'] ?? $v['pan_number'] ?? '') ?>"></div>
        <div class="col-md-6">
          <label class="form-label">Seller Type</label>
          <select name="seller_type" class="form-select">
            <option value="commission" <?= ($v['seller_type']??'commission')==='commission'?'selected':'' ?>>Commission (% per order)</option>
            <option value="subscription" <?= ($v['seller_type']??'')==='subscription'?'selected':'' ?>>Subscription (0% commission while active)</option>
          </select>
        </div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($v['description'] ?? '') ?></textarea></div>
      </div></div></div>
      <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="bi bi-geo-alt me-2"></i>Registered Address <small class="text-muted">(used for GST place-of-supply)</small></span></div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Shop Phone</label><input type="text" name="shop_phone" class="form-control" value="<?= e($old['shop_phone'] ?? $v['shop_phone'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Pincode</label><input type="text" name="pincode" class="form-control" value="<?= e($old['pincode'] ?? $v['pincode'] ?? '') ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= e($old['address'] ?? $v['address'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= e($old['city'] ?? $v['city'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">State</label><input type="text" name="state" class="form-control" value="<?= e($old['state'] ?? $v['state'] ?? '') ?>"></div>
      </div></div></div>
      <div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Save Changes</button><a href="<?= $p ?>/sellers/<?= $v['id'] ?>" class="btn btn-outline-secondary">Cancel</a></div>
    </form>
  </div>
  <div class="col-lg-4"><div class="card"><div class="card-header"><span class="card-title">Current Status</span></div><div class="card-body">
    <div class="mb-2"><?= statusBadge($v['status'] ?? 'pending') ?></div>
    <p style="font-size:12px;color:var(--muted);margin-bottom:0;">Approve/reject/suspend actions are on the seller's detail page — this form only edits account and shop details.</p>
  </div></div></div>
</div>
