<?php include __DIR__.'/_sidebar.php'; ?>
<div class="card">
  <div class="card-head"><span class="card-title">📍 My Addresses</span></div>
  <div class="card-body">
    <div class="addr-grid">
      <?php foreach($addresses as $a): ?>
      <div class="addr-card <?= $a['is_default']?'default':'' ?>">
        <div class="addr-type-badge"><?= e($a['label']) ?><?php if($a['is_default']): ?><span class="addr-default-tag">Default</span><?php endif; ?></div>
        <div class="addr-name"><?= e($a['recipient_name']) ?></div>
        <div class="addr-line"><?= e($a['address_line1']) ?><br><?= e($a['city'].', '.$a['state'].' - '.$a['pincode']) ?></div>
        <div class="addr-phone">📞 <?= e($a['phone']) ?></div>
        <div class="addr-actions">
          <?php if(!$a['is_default']): ?><button class="addr-btn def" data-action="addr-default" data-addr-id="<?= $a['id'] ?>">Set Default</button><?php endif; ?>
          <button class="addr-btn del" data-action="addr-delete" data-addr-id="<?= $a['id'] ?>">Delete</button>
        </div>
      </div>
      <?php endforeach; ?>
      <div class="add-addr-card" data-action="modal-open" data-target="addAddrModal"><div class="plus">+</div><p>Add New Address</p></div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal-overlay" id="addAddrModal">
  <div class="modal-box">
    <div class="modal-title">➕ Add New Address</div>
    <form method="POST" action="<?= APP_URL ?>/account/addresses" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group full"><label class="form-label">Label (Home/Work/Other)</label><input type="text" name="label" class="form-control" placeholder="Home" value="Home" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">Recipient Name *</label><input type="text" name="recipient_name" class="form-control" required placeholder="Full Name" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">Phone *</label><input type="tel" name="phone" class="form-control" required placeholder="+91 ..." oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group full"><label class="form-label">Address *</label><input type="text" name="address_line1" class="form-control" required placeholder="House No, Street, Area" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">City *</label><input type="text" name="city" class="form-control" required oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">State *</label><input type="text" name="state" class="form-control" required oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">Pincode *</label><input type="text" name="pincode" class="form-control" required maxlength="6" pattern="\d{6}" data-pattern-message="Enter a valid 6-digit pincode." oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group end"><label class="check-label"><input type="checkbox" name="is_default" value="1"> Set as default</label></div>
      </div>
      <button type="submit" class="btn-save wide">Save Address</button>
    </form>
    <button class="btn-cancel" data-action="modal-close" data-target="addAddrModal">Cancel</button>
  </div>
</div>
<?php $scripts = '<script src="'.asset('frontend/js/account.js').'"></script>'; ?>
    </div></div></div>
