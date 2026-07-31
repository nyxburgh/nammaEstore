<?php $p = ADMIN_URL; $isEdit = !empty($product); ?>
<div class="d-flex align-items-center gap-3 mb-4">
  <a href="<?= $p ?>/products<?= $isEdit ? '/'.$product['id'] : '' ?>" class="btn btn-sm btn-outline-secondary btn-icon"><i class="bi bi-arrow-left"></i></a>
  <div class="flex-fill"><h5 style="font-weight:800;margin:0;"><?= e($title) ?></h5></div>
</div>

<form method="POST" action="<?= $isEdit ? "$p/products/{$product['id']}/edit" : "$p/products" ?>" enctype="multipart/form-data" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="row g-3">

    <!-- LEFT: Main fields -->
    <div class="col-lg-8">

      <!-- Basic Info -->
      <div class="card mb-3">
        <div class="card-header"><span class="card-title">Basic Information</span></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Product Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="e.g. Samsung 55-inch 4K Smart TV" value="<?= e($product['name'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)">
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-600">Seller *</label>
              <select name="seller_id" class="form-select" required oninput="validateField(this)" onblur="validateField(this)">
                <option value="">Select seller</option>
                <?php foreach($sellers as $v): ?>
                <option value="<?= $v['id'] ?>" <?= ($product['seller_id']??'') == $v['id'] ? 'selected' : '' ?>>
                  <?= e($v['shop_name'] ?: $v['name']) ?> (<?= e($v['email']) ?>)
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Category *</label>
              <select name="category_id" class="form-select" required oninput="validateField(this)" onblur="validateField(this)">
                <option value="">Select category</option>
                <?php foreach($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($product['category_id']??'') == $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Brand</label>
              <select name="brand_id" class="form-select">
                <option value="">— none —</option>
                <?php foreach(($brands ?? []) as $br): ?>
                <option value="<?= $br['id'] ?>" <?= ($product['brand_id']??'') == $br['id'] ? 'selected' : '' ?>><?= e($br['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">SKU / Model No.</label>
              <input type="text" name="sku" class="form-control" placeholder="Unique product code" value="<?= e($product['sku'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Barcode</label>
              <input type="text" name="barcode" class="form-control" placeholder="EAN/UPC barcode" value="<?= e($product['barcode'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">Weight (kg)</label>
              <input type="number" name="weight" class="form-control" step="0.001" placeholder="0.500" value="<?= e($product['weight'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">HSN Code</label>
              <input type="text" name="hsn_code" class="form-control" placeholder="e.g. 6109" value="<?= e($product['hsn_code'] ?? '') ?>">
              <small class="text-muted">Required for GST invoices. Look up at <a href="https://cbic-gst.gov.in/gst-goods-services-rates.html" target="_blank" rel="noopener">CBIC GST rate finder</a>.</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-600">GST Rate (%) *</label>
              <select name="gst_rate" class="form-select" required oninput="validateField(this)" onblur="validateField(this)">
                <?php foreach([0,5,12,18,28] as $rate): ?>
                <option value="<?= $rate ?>" <?= (float)($product['gst_rate'] ?? 18) === (float)$rate ? 'selected' : '' ?>><?= $rate ?>%</option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="mt-3">
            <label class="form-label fw-600">Description</label>
            <textarea name="description" class="form-control" rows="5" placeholder="Detailed product description..."><?= e($product['description'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Pricing & Stock -->
      <div class="card mb-3">
        <div class="card-header"><span class="card-title">Pricing & Stock</span></div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label fw-600">Price (MRP) *</label>
              <div class="input-group"><span class="input-group-text">₹</span>
              <input type="number" name="price" id="price" class="form-control" required step="0.01" min="0" placeholder="0.00" value="<?= e($product['price'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">Sale Price</label>
              <div class="input-group"><span class="input-group-text">₹</span>
              <input type="number" name="sale_price" class="form-control" step="0.01" min="0" placeholder="0.00 (optional)" value="<?= e($product['sale_price'] ?? '') ?>" data-lte-field="#price" oninput="validateField(this)" onblur="validateField(this)"></div>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-600">Stock Quantity *</label>
              <input type="number" name="stock" class="form-control" required min="0" placeholder="0" value="<?= e($product['stock'] ?? '0') ?>" oninput="validateField(this)" onblur="validateField(this)">
            </div>
          </div>
        </div>
      </div>

      <!-- Images -->
      <div class="card mb-3">
        <div class="card-header"><span class="card-title">Product Images</span></div>
        <div class="card-body">
          <?php if($isEdit && !empty($product['images'])): ?>
          <div class="d-flex flex-wrap gap-2 mb-3">
            <?php foreach($product['images'] as $img): ?>
            <div class="position-relative">
              <img src="<?= UPLOAD_URL.'/'.$img['image_path'] ?>" style="width:72px;height:72px;object-fit:cover;border-radius:9px;border:2px solid <?= $img['is_primary']?'var(--purple)':'var(--border)' ?>;">
              <?php if($img['is_primary']): ?><div style="position:absolute;bottom:2px;left:50%;transform:translateX(-50%);background:var(--purple);color:#fff;font-size:8px;font-weight:700;padding:1px 5px;border-radius:4px;white-space:nowrap;">Primary</div><?php endif; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="form-text mb-2">Current images shown above. Upload new ones below to add more.</div>
          <?php endif; ?>
          <div class="upload-drop border rounded p-4 text-center" style="border-style:dashed!important;cursor:pointer;background:var(--bg);" onclick="this.querySelector('input').click()">
            <i class="bi bi-cloud-upload" style="font-size:28px;color:var(--muted);"></i>
            <div style="font-weight:600;margin-top:6px;font-size:13px;">Click to upload images</div>
            <div style="font-size:12px;color:var(--muted);">JPG, PNG, WebP — max 2MB each</div>
            <input type="file" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
          </div>
          <div id="imgPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
        </div>
      </div>

      <!-- Variants -->
      <div class="card mb-3">
        <div class="card-header d-flex align-items-center justify-content-between">
          <span class="card-title">Variants <span style="font-size:11px;color:var(--muted);font-weight:400;">(Size, Color, etc.)</span></span>
          <button type="button" class="btn btn-sm btn-outline-primary" onclick="addVariant()"><i class="bi bi-plus-lg"></i> Add Variant</button>
        </div>
        <div class="card-body">
          <div id="variantRows">
            <?php if($isEdit && !empty($product['variants'])): ?>
            <?php foreach($product['variants'] as $i => $v): ?>
            <div class="row g-2 mb-2 variant-row align-items-center">
              <div class="col"><input type="text" class="form-control form-control-sm" name="variants[<?= $i ?>][name]" placeholder="Attribute (Size)" value="<?= e($v['variant_name']) ?>"></div>
              <div class="col"><input type="text" class="form-control form-control-sm" name="variants[<?= $i ?>][value]" placeholder="Value (XL)" value="<?= e($v['variant_value']) ?>"></div>
              <div class="col"><div class="input-group input-group-sm"><span class="input-group-text">±₹</span><input type="number" class="form-control" name="variants[<?= $i ?>][price_modifier]" step="0.01" placeholder="0" value="<?= e($v['price_modifier']) ?>"></div></div>
              <div class="col"><input type="number" class="form-control form-control-sm" name="variants[<?= $i ?>][stock]" placeholder="Stock" min="0" value="<?= e($v['stock']) ?>" oninput="validateField(this)" onblur="validateField(this)"></div>
              <div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="this.closest('.variant-row').remove()"><i class="bi bi-x"></i></button></div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <small class="text-muted">Price modifier: extra cost added to base price (e.g. +50 for XL size)</small>
        </div>
      </div>
    </div>

    <!-- RIGHT: Settings -->
    <div class="col-lg-4">
      <div class="card mb-3" style="position:sticky;top:70px;">
        <div class="card-header"><span class="card-title">Settings</span></div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-600">Status</label>
            <select name="status" class="form-select">
              <?php foreach(['active'=>'Active — Live on site','draft'=>'Draft — Hidden','inactive'=>'Inactive — Unlisted','rejected'=>'Rejected'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($product['status']??'draft')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="featured" <?= !empty($product['is_featured'])?'checked':'' ?>>
            <label class="form-check-label fw-600" for="featured">⭐ Featured Product</label>
            <div class="form-text">Appears in featured sections on homepage</div>
          </div>
          <div class="d-grid gap-2 mt-3">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? '💾 Save Changes' : '🚀 Create Product' ?></button>
            <a href="<?= $p ?>/products<?= $isEdit ? '/'.$product['id'] : '' ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</form>

<?php
$varIdx = $isEdit ? count($product['variants'] ?? []) : 0;
$scripts = <<<JS
<script>
let varIdx = {$varIdx};
function addVariant() {
  const row = `<div class="row g-2 mb-2 variant-row align-items-center">
    <div class="col"><input type="text" class="form-control form-control-sm" name="variants[\${varIdx}][name]" placeholder="Attribute (Size)"></div>
    <div class="col"><input type="text" class="form-control form-control-sm" name="variants[\${varIdx}][value]" placeholder="Value (XL)"></div>
    <div class="col"><div class="input-group input-group-sm"><span class="input-group-text">±₹</span><input type="number" class="form-control" name="variants[\${varIdx}][price_modifier]" step="0.01" placeholder="0"></div></div>
    <div class="col"><input type="number" class="form-control form-control-sm" name="variants[\${varIdx}][stock]" placeholder="Stock" min="0" value="0" oninput="validateField(this)" onblur="validateField(this)"></div>
    <div class="col-auto"><button type="button" class="btn btn-sm btn-outline-danger btn-icon" onclick="this.closest('.variant-row').remove()"><i class="bi bi-x"></i></button></div>
  </div>`;
  document.getElementById('variantRows').insertAdjacentHTML('beforeend', row);
  varIdx++;
}
function previewImages(input) {
  const prev = document.getElementById('imgPreview');
  prev.innerHTML = '';
  for (const f of input.files) {
    const url = URL.createObjectURL(f);
    prev.insertAdjacentHTML('beforeend', `<img src="\${url}" style="width:72px;height:72px;object-fit:cover;border-radius:9px;border:2px solid var(--border);">`);
  }
}
</script>
JS;
?>
