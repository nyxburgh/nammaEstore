<?php
// Shared form fields for pages create/edit. Expects $pg (array|null).
$pg = $pg ?? null;
?>
<div class="row g-3 mb-3">
  <div class="col-md-6"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required maxlength="150" value="<?= e($pg['title']??'') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
  <div class="col-md-3"><label class="form-label">Icon (emoji)</label><input type="text" name="icon" class="form-control" maxlength="16" value="<?= e($pg['icon']??'ℹ️') ?>"></div>
  <div class="col-md-3"><label class="form-label">Sort Order</label><input type="number" name="sort_order" class="form-control" value="<?= $pg['sort_order']??0 ?>" min="0" oninput="validateField(this)" onblur="validateField(this)"></div>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-6"><label class="form-label">Slug</label><div class="input-group"><span class="input-group-text">/info/</span><input type="text" name="slug" class="form-control" maxlength="100" placeholder="auto from title" value="<?= e($pg['slug']??'') ?>" pattern="[a-z0-9-]*" data-pattern-message="Lowercase letters, numbers and hyphens only." oninput="validateField(this)" onblur="validateField(this)"></div></div>
  <div class="col-md-6"><label class="form-label">Short Description</label><input type="text" name="short_description" class="form-control" maxlength="255" value="<?= e($pg['short_description']??'') ?>" placeholder="Shown on the Info Center card" oninput="validateField(this)" onblur="validateField(this)"></div>
</div>
<div class="mb-2"><label class="form-label">Content (HTML)</label>
  <textarea name="content" class="form-control" rows="18" style="font-family:monospace;font-size:13px;" oninput="validateField(this)" onblur="validateField(this)"><?= e($pg['content']??'') ?></textarea>
</div>
<div class="mb-4" style="font-size:12.5px;color:var(--muted);">
  Allowed tokens (replaced automatically on the storefront):
  <code>{{site_name}}</code> <code>{{app_url}}</code> <code>{{vendor_url}}</code> <code>{{site_email}}</code> <code>{{site_phone}}</code>.
  Use headings as <code>&lt;h2&gt;</code>, paragraphs as <code>&lt;p&gt;</code>, lists as <code>&lt;ul&gt;&lt;li&gt;</code>.
</div>
