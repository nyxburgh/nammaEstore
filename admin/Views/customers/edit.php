<?php $p=ADMIN_URL; $c=$customer; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/customers/<?= $c['id'] ?>" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><h5 style="font-weight:800;margin:0;">Edit Customer: <?= e($old['name'] ?? $c['name']) ?></h5></div>
<div class="row"><div class="col-lg-6"><div class="card"><div class="card-header"><span class="card-title">Customer Details</span></div><div class="card-body">
  <form method="POST" action="<?= $p ?>/customers/<?= $c['id'] ?>/edit" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
    <div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= e($old['name'] ?? $c['name']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($old['email'] ?? $c['email']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="mb-3"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= e($old['phone'] ?? $c['phone'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="mb-3"><label class="form-label">Status</label>
      <select name="is_active" class="form-select">
        <option value="1" <?= ($c['is_active']??1)==1?'selected':'' ?>>Active</option>
        <option value="0" <?= ($c['is_active']??1)==0?'selected':'' ?>>Inactive</option>
      </select>
    </div>
    <div class="mb-4"><label class="form-label">Reset Password</label><input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep current" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">Save Changes</button><a href="<?= $p ?>/customers/<?= $c['id'] ?>" class="btn btn-outline-secondary">Cancel</a></div>
  </form>
</div></div></div></div>
