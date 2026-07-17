<h2 class="auth-title">Start Selling Today 🚀</h2>
<p class="auth-sub">Create your vendor account and reach thousands of buyers</p>
<form method="POST" action="<?= VENDOR_URL ?>/register">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="form-group full">
      <label class="form-label">Shop / Brand Name *</label>
      <input type="text" name="shop_name" class="form-control" required placeholder="e.g. TechMart Store" value="<?= e($_POST['shop_name'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Your Name *</label>
      <input type="text" name="name" class="form-control" required placeholder="Full name" value="<?= e($_POST['name'] ?? '') ?>">
    </div>
    <div class="form-group">
      <label class="form-label">Phone *</label>
      <input type="tel" name="phone" class="form-control" required placeholder="+91 ..." value="<?= e($_POST['phone'] ?? '') ?>">
    </div>
    <div class="form-group full">
      <label class="form-label">Email Address *</label>
      <input type="email" name="email" class="form-control" required placeholder="store@email.com" value="<?= e($_POST['email'] ?? '') ?>">
    </div>
    <div class="form-group full">
      <label class="form-label">Shop Description</label>
      <textarea name="description" class="form-control" rows="2" placeholder="Briefly describe what you sell..."><?= e($_POST['description'] ?? '') ?></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Password *</label>
      <input type="password" name="password" class="form-control" required placeholder="Min 8 characters">
    </div>
    <div class="form-group">
      <label class="form-label">Confirm Password *</label>
      <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat password">
    </div>
  </div>
  <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:9px;padding:10px 14px;margin:12px 0;font-size:.75rem;color:#15803d;">
    ✅ <strong>Free Plan:</strong> Start for ₹0/month. First ₹10,000 turnover at 0% commission.
  </div>
  <button type="submit" class="btn-auth">Create Vendor Account →</button>
</form>
<div class="auth-alt">Already a vendor? <a href="<?= VENDOR_URL ?>/login">Sign in</a></div>
