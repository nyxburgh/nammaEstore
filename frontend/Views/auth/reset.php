<h2 class="auth-title">Reset Password 🔑</h2>
<p class="auth-sub">Enter the 6-digit code sent to <strong><?= e($email) ?></strong> and choose a new password.</p>
<form method="POST" action="<?= APP_URL ?>/reset-password" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Reset Code</label>
    <input type="text" name="otp" class="form-control otp-input" required placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" data-pattern="\d{6}" data-pattern-message="Enter the 6-digit code." oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <div class="form-group">
    <label class="form-label">New Password</label>
    <input type="password" name="new_password" id="reset_new_password" class="form-control" required minlength="8" placeholder="Minimum 8 characters" oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <div class="form-group">
    <label class="form-label">Confirm New Password</label>
    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password" data-equal-to="#reset_new_password" oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <button type="submit" class="btn-auth">Reset Password →</button>
</form>
<div class="auth-alt"><a href="<?= APP_URL ?>/forgot-password">Didn't get a code? Try again</a></div>
