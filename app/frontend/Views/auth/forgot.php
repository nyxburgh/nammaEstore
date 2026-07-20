<h2 class="auth-title">Forgot Password 🔒</h2>
<p class="auth-sub">Enter your email and we'll send you a 6-digit reset code</p>
<form method="POST" action="<?= APP_URL ?>/forgot-password" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" required placeholder="you@email.com" oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <button type="submit" class="btn-auth">Send Reset Code →</button>
</form>
<div class="auth-alt"><a href="<?= APP_URL ?>/login">← Back to Login</a></div>
