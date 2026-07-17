<?php $p=ADMIN_URL; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 style="font-weight:800;margin:0;">Issue Gift Voucher</h5>
  <a href="<?= $p ?>/gift-cards" class="btn btn-outline-secondary btn-sm">← Back</a>
</div>

<form method="POST" action="<?= $p ?>/gift-cards/create">
  <?= csrf_field() ?>
  <div class="card"><div class="card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Type *</label>
        <select name="type" id="typeSelect" class="form-select" onchange="document.getElementById('vendorWrap').style.display=this.value==='vendor'?'':'none'" required>
          <option value="company">Company Gift Card</option>
          <option value="vendor">Vendor Gift Card</option>
          <option value="recharge">Recharge Gift Card</option>
        </select>
      </div>
      <div class="col-md-4" id="vendorWrap" style="display:none;">
        <label class="form-label">Vendor *</label>
        <select name="vendor_id" class="form-select">
          <option value="">— select vendor —</option>
          <?php foreach(($vendors ?? []) as $v): ?>
          <option value="<?= $v['id'] ?>"><?= e($v['shop_name'] ?: $v['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-4">
        <label class="form-label">Amount (₹) *</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
      </div>
      <div class="col-md-6">
        <label class="form-label">Issue to (email, optional)</label>
        <input type="email" name="issued_to_email" class="form-control" placeholder="customer@example.com">
      </div>
      <div class="col-md-6">
        <label class="form-label">Expires</label>
        <input type="datetime-local" name="expires_at" class="form-control">
      </div>
    </div>
  </div></div>
  <div class="mt-3">
    <button type="submit" class="btn btn-primary">Issue Voucher</button>
    <a href="<?= $p ?>/gift-cards" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
