<h2 class="auth-title">Welcome Back 👋</h2>
<p class="auth-sub">Sign in to your seller account</p>
<form method="POST" action="<?= VENDOR_URL ?>/login">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" required placeholder="your@store.com" value="<?= e($_POST['email'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required placeholder="Your password">
  </div>
  <button type="submit" class="btn-auth">Sign In to Dashboard →</button>
</form>
<div class="auth-alt" style="margin-top:14px;">New seller? <a href="<?= VENDOR_URL ?>/register">Create vendor account</a></div>
<div class="divider"><span>Or</span></div>
<div class="auth-alt"><a href="<?= APP_URL ?>" style="color:var(--gray);font-weight:400;">← Back to Marketplace</a></div>
