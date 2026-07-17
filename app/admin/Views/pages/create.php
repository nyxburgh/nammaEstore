<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center gap-3 mb-4"><a href="<?= $p ?>/pages" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a><h5 style="font-weight:800;margin:0;">Add Page</h5></div>
<div class="row"><div class="col-lg-9"><div class="card"><div class="card-header"><span class="card-title">Page Details</span></div><div class="card-body">
<form method="POST" action="<?= $p ?>/pages">
  <?= csrf_field() ?>
  <?php $pg=null; include __DIR__.'/_form.php'; ?>
  <input type="hidden" name="is_active" value="1">
  <div class="d-flex gap-2"><button type="submit" class="btn btn-primary px-4">Create Page</button><a href="<?= $p ?>/pages" class="btn btn-outline-secondary">Cancel</a></div>
</form>
</div></div></div></div>
