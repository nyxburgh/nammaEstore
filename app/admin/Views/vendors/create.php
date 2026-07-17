<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/vendors" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><div><h5 style="font-weight:800;margin:0;">Add New Vendor</h5></div></div>
<div class="row g-3">
  <div class="col-lg-8">
    <form method="POST" action="<?= $p ?>/vendors">
  <?= csrf_field() ?>
      <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="bi bi-person me-2"></i>Account Details</span></div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= e($_POST['name']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($_POST['email']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= e($_POST['phone']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required placeholder="Min 8 characters"></div>
      </div></div></div>
      <div class="card mb-3"><div class="card-header"><span class="card-title"><i class="bi bi-shop me-2"></i>Shop Details</span></div><div class="card-body"><div class="row g-3">
        <div class="col-md-6"><label class="form-label">Shop Name *</label><input type="text" name="shop_name" class="form-control" required value="<?= e($_POST['shop_name']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">GST Number</label><input type="text" name="gst_number" class="form-control" value="<?= e($_POST['gst_number']??'') ?>"></div>
        <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"><?= e($_POST['description']??'') ?></textarea></div>
      </div></div></div>
      <div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Create Vendor</button><a href="<?= $p ?>/vendors" class="btn btn-outline-secondary">Cancel</a></div>
    </form>
  </div>
  <div class="col-lg-4"><div class="card"><div class="card-header"><span class="card-title">Auto Assignment</span></div><div class="card-body">
    <div style="background:var(--purple-soft);border-radius:10px;padding:14px;text-align:center;"><div style="font-size:10px;color:var(--purple);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Default Plan</div><div style="font-size:20px;font-weight:800;color:var(--purple);">Free Plan</div><div style="font-size:12.5px;color:var(--muted);margin-top:4px;">10% commission on all sales</div></div>
    <p style="font-size:12px;color:var(--muted);margin-top:12px;margin-bottom:0;">Vendor can upgrade from their dashboard.</p>
  </div></div></div>
</div>
