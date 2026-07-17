<h2 class="auth-title">Create Account ✨</h2>
<p class="auth-sub">Join millions of shoppers on Namma E Store</p>
<form method="POST" action="<?= APP_URL ?>/register">
  <?= csrf_field() ?>
  <div class="form-group">
    <label class="form-label">Full Name</label>
    <input type="text" name="name" class="form-control" required placeholder="Rahul Sharma" value="<?= e($_POST['name'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label class="form-label">Email Address</label>
    <input type="email" name="email" class="form-control" required placeholder="you@email.com" value="<?= e($_POST['email'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label class="form-label">Phone (optional)</label>
    <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210" value="<?= e($_POST['phone'] ?? '') ?>">
  </div>
  <div class="form-group">
    <label class="form-label">Password</label>
    <input type="password" name="password" class="form-control" required placeholder="Minimum 8 characters">
  </div>
  <div class="form-group">
    <label class="form-label">Confirm Password</label>
    <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
  </div>
  <div class="form-group"><?= \App\Core\Captcha::field() ?></div>
  <button type="submit" class="btn-auth">Create Account →</button>
</form>
<div class="auth-alt">Already have an account? <a href="<?= APP_URL ?>/login">Sign in</a></div>
