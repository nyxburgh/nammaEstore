<form action="<?= ADMIN_URL ?>/login" method="POST">
  <?= csrf_field() ?>
  <div class="form-group">
    <label>Email Address</label>
    <div class="input-wrap">
      <i class="bi bi-envelope icon"></i>
      <input type="email" name="email" placeholder="admin@mycart.com" required value="<?= e($_POST['email']??'') ?>">
    </div>
  </div>
  <div class="form-group">
    <label>Password</label>
    <div class="input-wrap">
      <i class="bi bi-lock icon"></i>
      <input type="password" name="password" placeholder="••••••••" required>
      <button type="button" class="eye-btn" onclick="togglePwd(this)"><i class="bi bi-eye"></i></button>
    </div>
  </div>
  <button type="submit" class="btn-submit"><i class="bi bi-box-arrow-in-right" style="margin-right:8px;"></i>Sign In to Admin Panel</button>
</form>
