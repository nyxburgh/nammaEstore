<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($title??'Seller') ?> — Namma E Store Seller Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('seller-panel/css/auth.css') ?>">
</head>
<body>

<!-- AUTH HEADER -->
<header class="auth-header">
  <a href="<?= APP_URL ?>" class="ah-logo">Namma <span>E</span> Store</a>
  <nav class="ah-nav">
    <a href="<?= APP_URL ?>" class="ah-link ah-home">← Back to Store</a>
    <a href="<?= APP_URL ?>/info/faq" class="ah-link">❓ Seller FAQ</a>
    <a href="<?= APP_URL ?>/info/contact-us" class="ah-link">📞 Support</a>
  </nav>
</header>

<main class="auth-main">
  <div class="auth-wrap">
    <div class="auth-brand">
      <a href="<?= APP_URL ?>" class="auth-logo">Namma <span>E</span> Store</a>
      <div class="auth-tagline">Multi-Seller Marketplace</div>
      <div class="seller-chip">🏪 Seller Panel</div>
    </div>
    <div class="auth-card">
      <?php if(!empty($_SESSION['flash'])): $fl=$_SESSION['flash']; unset($_SESSION['flash']); ?>
      <div class="alert alert-<?= $fl['type']==='success'?'success':'danger' ?>"><?= e($fl['message']) ?></div>
      <?php endif; ?>
      <?= $content ?>
    </div>
  </div>
</main>

<!-- AUTH FOOTER -->
<footer class="auth-footer">
  <nav class="af-links">
    <a href="<?= APP_URL ?>/info/privacy-policy">Privacy Policy</a>
    <a href="<?= APP_URL ?>/info/terms-conditions">Terms &amp; Conditions</a>
    <a href="<?= APP_URL ?>/info/contact-us">Contact Us</a>
    <a href="<?= APP_URL ?>/info/faq">FAQ</a>
  </nav>
  <div class="af-copy">&copy; <?= date('Y') ?> Namma E Store. All rights reserved.</div>
</footer>
</body>
</html>
