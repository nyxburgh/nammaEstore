<?php $p=ADMIN_URL; $b=$brand ?? []; $isEdit=!empty($b); ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 style="font-weight:800;margin:0;"><?= $isEdit?'Edit':'Add' ?> Brand</h5>
  <a href="<?= $p ?>/brands" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<form method="POST" action="<?= $isEdit ? $p.'/brands/'.$b['id'].'/edit' : $p.'/brands/create' ?>" enctype="multipart/form-data" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="card"><div class="card-body">
    <div class="row g-3">
      <div class="col-md-8">
        <label class="form-label">Brand Name *</label>
        <input type="text" name="name" class="form-control" value="<?= e($b['name'] ?? '') ?>" required oninput="validateField(this)" onblur="validateField(this)">
      </div>
      <div class="col-md-4">
        <label class="form-label">Logo</label>
        <input type="file" name="logo" class="form-control" accept="image/*">
        <?php if(!empty($b['logo'])): ?><img src="<?= UPLOAD_URL.'/'.$b['logo'] ?>" alt="<?= e($b['name'] ?? 'Brand logo') ?>" style="width:40px;margin-top:6px;"><?php endif; ?>
      </div>
      <div class="col-12">
        <label class="form-check">
          <input type="checkbox" name="is_active" class="form-check-input" value="1" <?= ($b['is_active']??1)?'checked':'' ?>>
          <span class="form-check-label">Active</span>
        </label>
      </div>
    </div>
  </div></div>
  <div class="mt-3">
    <button type="submit" class="btn btn-primary"><?= $isEdit?'Update':'Create' ?> Brand</button>
    <a href="<?= $p ?>/brands" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
