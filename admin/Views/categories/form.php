<?php
$p = ADMIN_URL; $isEdit = !empty($category);
$parentCategory = $parentCategory ?? null;
$isSub = !empty($parentCategory);
$noun = $isSub ? 'Sub Category' : 'Category';
?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= $p ?>/categories" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a>
  <h5 style="font-weight:800;margin:0;"><?= e($title) ?></h5>
</div>

<div class="row justify-content-center">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header"><span class="card-title"><?= $isEdit ? 'Edit' : 'New' ?> <?= $noun ?></span></div>
      <div class="card-body">
        <?php if($isSub): ?>
        <div class="mb-3 small text-muted">Category: <strong class="text-dark"><?= e($parentCategory['name']) ?></strong></div>
        <?php endif; ?>
        <form method="POST" action="<?= $isEdit ? "$p/categories/{$category['id']}" : "$p/categories" ?>" onsubmit="return validateForm(this)">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label class="form-label fw-600"><?= $noun ?> Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Electronics" value="<?= e($category['name'] ?? $_POST['name'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)">
          </div>
          <?php if($isSub && !$isEdit): ?>
          <input type="hidden" name="parent_id" value="<?= (int)$parentCategory['id'] ?>">
          <?php else: ?>
          <div class="mb-3">
            <label class="form-label fw-600">Parent Category</label>
            <select name="parent_id" class="form-select">
              <option value="">None (Top Level)</option>
              <?php foreach($parents as $par): ?>
              <option value="<?= $par['id'] ?>" <?= (($category['parent_id'] ?? $_GET['parent_id'] ?? '') == $par['id']) ? 'selected' : '' ?>><?= e($par['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <label class="form-label fw-600">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Optional description..."><?= e($category['description'] ?? '') ?></textarea>
          </div>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?= $category['sort_order'] ?? 0 ?>" min="0" oninput="validateField(this)" onblur="validateField(this)">
              <div class="form-text">Lower number = appears first</div>
            </div>
            <div class="col-md-6 d-flex align-items-end pb-2">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" <?= ($category['is_active'] ?? 1) ? 'checked' : '' ?>>
                <label class="form-check-label fw-600" for="isActive">Active (visible to customers)</label>
              </div>
            </div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Save Changes' : "Create {$noun}" ?></button>
            <a href="<?= $p ?>/categories" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
