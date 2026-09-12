<h2 class="auth-title">Welcome Back 👋</h2>
<p class="auth-sub">Sign in to your seller account</p>
<form method="POST" action="<?= SELLER_URL ?>/login">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" required placeholder="your@store.com" value="<?= e($_POST['email'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label class="form-label">Password</label>
    <div class="password-field-wrap">
      <input type="password" name="password" id="login_password" class="form-control" required placeholder="Your password">
      <button type="button" class="password-toggle-btn" data-action="toggle-password" data-target="login_password" aria-label="Show password">👁️</button>
    </div>
  </div>
  <button type="submit" class="btn-auth">Sign In to Dashboard →</button>
</form>
<div class="auth-alt" style="margin-top:14px;">New seller? <a href="<?= SELLER_URL ?>/register">Create seller account</a></div>
