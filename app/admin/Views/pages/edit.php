<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/pages" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><h5 style="font-weight:800;margin:0;">Edit Page</h5><a href="<?= APP_URL ?>/info/<?= e($pageRow['slug']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary ms-auto"><i class="bi bi-box-arrow-up-right me-1"></i>View on site</a></div>
<div class="row"><div class="col-lg-9"><div class="card"><div class="card-header"><span class="card-title">Page Details</span></div><div class="card-body">
<form method="POST" action="<?= $p ?>/pages/<?= $pageRow['id'] ?>" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <?php $pg=$pageRow; include __DIR__.'/_form.php'; ?>
  <div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4">Save Changes</button><a href="<?= $p ?>/pages" class="btn btn-outline-secondary">Cancel</a></div>
</form>
</div></div></div></div>
