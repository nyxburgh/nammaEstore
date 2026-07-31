<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/banners" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><h5 style="font-weight:800;margin:0;">Edit Banner</h5></div>
<div class="row"><div class="col-lg-7"><div class="card"><div class="card-header"><span class="card-title">Banner Details</span></div><div class="card-body">
<form method="POST" action="<?= $p ?>/banners/<?= $banner['id'] ?>" enctype="multipart/form-data" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required value="<?= e($banner['title']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="mb-3"><label class="form-label">Current Image</label><br><img src="<?= UPLOAD_URL.'/'.$banner['image_path'] ?>" alt="<?= e($banner['title']) ?> banner" style="max-width:100%;border-radius:8px;max-height:140px;object-fit:cover;margin-bottom:8px;border:1px solid var(--border);"><label class="form-label mt-2">Replace Image <span style="color:var(--muted);font-size:12px;">(optional)</span></label><input type="file" name="image" class="form-control" accept="image/*"></div>
  <div class="row g-3 mb-3">
    <div class="col-md-6"><label class="form-label">Position</label><select name="position" class="form-select" oninput="validateField(this)" onblur="validateField(this)"><?php foreach(['hero','sidebar','popup','top_bar'] as $pos): ?><option value="<?= $pos ?>" <?= $banner['position']===$pos?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$pos)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= $banner['sort_order'] ?>" min="0" oninput="validateField(this)" onblur="validateField(this)"></div>
  </div>
  <div class="mb-3"><label class="form-label">Link URL</label><input type="url" name="link_url" class="form-control" value="<?= e($banner['link_url']??'') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="row g-3 mb-4">
    <div class="col-md-6"><label class="form-label">Start Date</label><input type="datetime-local" name="starts_at" id="bannerStartsAt" class="form-control" value="<?= $banner['starts_at']?date('Y-m-d\TH:i',strtotime($banner['starts_at'])):'' ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="col-md-6"><label class="form-label">End Date</label><input type="datetime-local" name="ends_at" class="form-control" data-gte-field="#bannerStartsAt" value="<?= $banner['ends_at']?date('Y-m-d\TH:i',strtotime($banner['ends_at'])):'' ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  </div>
  <div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4">Save Changes</button><a href="<?= $p ?>/banners" class="btn btn-outline-secondary">Cancel</a></div>
</form>
</div></div></div></div>
