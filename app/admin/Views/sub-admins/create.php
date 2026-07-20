<?php $p=ADMIN_URL;
$moduleLabels=['products'=>'Product Management','payments'=>'Payment Monitoring','reports'=>'Reports & Analytics','activity'=>'Activity Logs','banners'=>'Banner Management'];
$moduleIcons=['products'=>'box-seam','payments'=>'credit-card','reports'=>'bar-chart','activity'=>'clock-history','banners'=>'images']; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/sub-admins" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><h5 style="font-weight:800;margin:0;">Add Sub-Admin</h5></div>
<form method="POST" action="<?= $p ?>/sub-admins" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-7"><div class="card"><div class="card-header"><span class="card-title">Account Details</span></div><div class="card-body"><div class="row g-3">
    <div class="col-md-6"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required value="<?= e($_POST['name']??'') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="col-md-6"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($_POST['email']??'') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required minlength="8" placeholder="Min 8 characters" oninput="validateField(this)" onblur="validateField(this)"></div>
  </div></div></div></div>
  <div class="col-lg-5"><div class="card"><div class="card-header"><span class="card-title">Module Access</span></div><div class="card-body" data-min-checked="1">
    <?php foreach($modules as $m): ?>
    <div style="background:var(--bg);border-radius:9px;padding:11px 13px;margin-bottom:10px;">
      <label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13.5px;cursor:pointer;margin:0;">
        <input type="checkbox" name="modules[]" value="<?= $m ?>" class="module-check" data-module="<?= $m ?>" style="width:16px;height:16px;">
        <i class="bi bi-<?= $moduleIcons[$m] ?>" style="color:var(--purple);"></i>
        <?= $moduleLabels[$m] ?>
      </label>
      <div id="p-<?= $m ?>" style="display:none;padding-left:24px;margin-top:8px;">
        <?php foreach(['create'=>'Create','edit'=>'Edit','delete'=>'Delete'] as $a=>$al): ?>
        <label style="font-size:12px;margin-right:14px;cursor:pointer;"><input type="checkbox" name="perm[<?= $m ?>][<?= $a ?>]" value="1"> <?= $al ?></label>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div></div></div>
  <div class="col-12"><div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4">Create Sub-Admin</button><a href="<?= $p ?>/sub-admins" class="btn btn-outline-secondary">Cancel</a></div></div>
</div>
</form>
<?php $scripts = '<script src="'.admin_asset('js/sub-admins-form.js').'"></script>'; ?>
