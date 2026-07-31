<h2 class="auth-title">Verify Your Email 📧</h2>
<p class="auth-sub">We sent a 6-digit code to <strong><?= e($email) ?></strong>. Enter it below to activate your shop.</p>
<form method="POST" action="<?= SELLER_URL ?>/verify-email">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Verification Code</label>
    <input type="text" name="otp" class="form-control" required placeholder="000000" maxlength="6" inputmode="numeric" style="letter-spacing:4px;font-size:1.2rem;text-align:center;">
  </div>
  <button type="submit" class="btn-auth">Verify & Activate Shop →</button>
</form>
<form method="POST" action="<?= SELLER_URL ?>/verify-email/resend" style="margin-top:14px;text-align:center;">
  <?= csrf_field() ?>
  <button type="submit" style="background:none;border:none;color:var(--pink-main);font-weight:600;font-size:.85rem;cursor:pointer;">Didn't get a code? Resend</button>
</form>
<div class="divider"><span>Or</span></div>
<div class="auth-alt"><a href="<?= SELLER_URL ?>/logout" style="color:var(--gray);font-weight:400;">Sign out</a></div>
