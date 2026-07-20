<h2 class="auth-title">Verify Your Email 📧</h2>
<p class="auth-sub">We sent a 6-digit code to <strong><?= e($email) ?></strong>. Enter it below to activate your account.</p>
<form method="POST" action="<?= APP_URL ?>/verify-email" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Verification Code</label>
    <input type="text" name="otp" class="form-control otp-input" required placeholder="000000" maxlength="6" inputmode="numeric" autocomplete="one-time-code" data-pattern="\d{6}" data-pattern-message="Enter the 6-digit code." oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <button type="submit" class="btn-auth">Verify & Continue →</button>
</form>
<form method="POST" action="<?= APP_URL ?>/verify-email/resend" class="resend-form">
  <?= csrf_field() ?>
  <button type="submit" class="btn-link">Didn&#39;t get a code? Resend</button>
</form>
<div class="auth-alt"><a href="<?= APP_URL ?>/login">← Back to Login</a></div>
