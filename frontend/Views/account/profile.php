<?php include __DIR__.'/_sidebar.php'; ?>
<div class="card">
  <div class="card-head"><span class="card-title">👤 Personal Information</span></div>
  <div class="card-body">
    <div class="avatar-section">
      <div class="av-circle"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      <div class="av-info"><div class="av-name"><?= e($user['name']) ?></div><div class="av-email"><?= e($user['email']) ?></div><div class="av-joined">Member since <?= date('M Y',strtotime($user['created_at']??'now')) ?></div></div>
    </div>
    <form method="POST" action="<?= APP_URL ?>/account/profile" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= e($old['name'] ?? $user['name']) ?>" required oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="<?= e($old['email'] ?? $user['email']) ?>" required oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">Phone Number</label><input type="tel" name="phone" class="form-control" value="<?= e($old['phone'] ?? $user['phone'] ?? '') ?>" placeholder="+91 ..." oninput="validateField(this)" onblur="validateField(this)"></div>
      </div>
      <div class="form-foot"><button type="submit" class="btn-save">Save Changes</button></div>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-head"><span class="card-title">🔒 Change Password</span></div>
  <div class="card-body">
    <form method="POST" action="<?= APP_URL ?>/account/profile/password/send-otp" class="otp-step">
  <?= csrf_field() ?>
      <p class="otp-hint">Step 1 — we'll email a 6-digit confirmation code to your registered address.</p>
      <button type="submit" class="btn-save outline">📧 Send Confirmation Code</button>
    </form>
    <form method="POST" action="<?= APP_URL ?>/account/profile/password" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group full"><label class="form-label">Email Confirmation Code</label><input type="text" name="otp" class="form-control" required placeholder="6-digit code from your email" maxlength="6" inputmode="numeric" autocomplete="one-time-code" data-pattern="\d{6}" data-pattern-message="Enter the 6-digit code." oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group full"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required placeholder="Enter current password" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">New Password</label><input type="password" name="new_password" id="profile_new_password" class="form-control" required minlength="8" placeholder="Min 8 characters" oninput="validateField(this)" onblur="validateField(this)"></div>
        <div class="form-group"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password" data-equal-to="#profile_new_password" oninput="validateField(this)" onblur="validateField(this)"></div>
      </div>
      <div class="form-foot"><button type="submit" class="btn-save">Update Password</button></div>
    </form>
  </div>
</div>
<div class="card">
  <div class="card-head"><span class="card-title danger">⚠️ Danger Zone</span></div>
  <div class="card-body">
    <p class="danger-text">These actions are irreversible. Please proceed with caution.</p>
    <button class="btn-danger-outline" data-action="account-delete-note">Delete Account</button>
  </div>
</div>
<?php $scripts = '<script src="'.asset('frontend/js/account.js').'"></script>'; ?>
    </div></div></div>
