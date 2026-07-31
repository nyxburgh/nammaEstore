<h2 class="auth-title">Welcome Back 👋</h2>
<p class="auth-sub">Sign in to your account to continue shopping</p>
<form method="POST" action="<?= APP_URL ?>/login" onsubmit="return validateForm(this)">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" id="login_email" class="form-control" required placeholder="you@email.com" value="<?= e($_POST['email'] ?? '') ?>" oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <div class="form-group">
    <label class="form-label">Password</label>
    <input type="password" name="password" id="login_password" class="form-control" required placeholder="Your password" oninput="validateField(this)" onblur="validateField(this)">
  </div>
  <?php if(!empty($showCaptcha)): ?>
  <div class="form-group"><?= \App\Core\Captcha::field() ?></div>
  <?php endif; ?>
  <div class="form-group form-check-inline">
    <label><input type="checkbox" name="remember" value="1"> Remember me for 30 days</label>
  </div>
  <button type="submit" class="btn-auth">Sign In →</button>
</form>
<div class="auth-alt">Don't have an account? <a href="<?= APP_URL ?>/register">Register now</a></div>
<div class="auth-alt auth-alt-secondary"><a href="<?= APP_URL ?>/forgot-password">Forgot password?</a></div>
