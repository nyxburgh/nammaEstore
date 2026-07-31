<?php $p=ADMIN_URL; $icons=['general'=>'gear','commission'=>'percent','orders'=>'bag-check','payment'=>'credit-card','settlement'=>'wallet2','gst'=>'receipt','inventory'=>'box-seam','shipping'=>'truck','loyalty'=>'star']; ?>
<div class="mb-4"><h5 style="font-weight:800;margin:0;">System Settings</h5><small class="text-muted">Configure global platform behaviour</small></div>
<form method="POST" action="<?= $p ?>/settings" onsubmit="return validateForm(this)">
  <?= csrf_field() ?><div class="row g-3">
  <?php foreach($grouped as $group=>$settings): ?>
  <div class="col-12"><div class="card"><div class="card-header"><span class="card-title"><i class="bi bi-<?= $icons[$group]??'sliders' ?> me-2"></i><?= ucfirst($group) ?> Settings</span></div><div class="card-body"><div class="row g-3">
    <?php foreach($settings as $s): ?>
    <div class="col-md-4"><label class="form-label"><?= e($s['label']??ucwords(str_replace('_',' ',$s['key']))) ?></label>
      <?php if($s['type']==='boolean'): ?><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="settings[<?= e($s['key']) ?>]" value="1" <?= $s['value']?'checked':'' ?>></div>
      <?php elseif($s['type']==='number'): ?><input type="number" step="0.01" name="settings[<?= e($s['key']) ?>]" class="form-control" value="<?= e($s['value']) ?>" oninput="validateField(this)" onblur="validateField(this)">
      <?php else: ?><input type="text" name="settings[<?= e($s['key']) ?>]" class="form-control" value="<?= e($s['value']) ?>" oninput="validateField(this)" onblur="validateField(this)">
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div></div></div></div>
  <?php endforeach; ?>
  <div class="col-12"><button type="submit" class="btn btn-primary px-5"><i class="bi bi-check-lg me-1"></i>Save All Settings</button></div>
</div></form>
