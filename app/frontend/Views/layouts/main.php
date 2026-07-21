<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= e($title??'Shop') ?></title>
<?php
$metaDesc  = $metaDescription ?? \App\Frontend\Services\SettingsService::get('site_description', 'Shop from thousands of trusted sellers on Namma E Store — India\'s multi-seller marketplace.');
$metaImg   = $metaImage ?? (FRONTEND_ASSETS . '/images/og-default.jpg');
$appPath   = parse_url(APP_URL, PHP_URL_PATH) ?: '';
$reqUri    = $_SERVER['REQUEST_URI'] ?? '/';
if ($appPath && str_starts_with($reqUri, $appPath)) $reqUri = substr($reqUri, strlen($appPath));
$canonical = $canonicalUrl ?? (APP_URL . $reqUri);
?>
<meta name="description" content="<?= e($metaDesc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">

<meta property="og:type" content="<?= e($ogType ?? 'website') ?>">
<meta property="og:title" content="<?= e($title??'Shop') ?>">
<meta property="og:description" content="<?= e($metaDesc) ?>">
<meta property="og:image" content="<?= e($metaImg) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:site_name" content="<?= e(\App\Frontend\Services\SettingsService::get('site_name','Namma E Store')) ?>">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title??'Shop') ?>">
<meta name="twitter:description" content="<?= e($metaDesc) ?>">
<meta name="twitter:image" content="<?= e($metaImg) ?>">

<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('frontend/css/main.css') ?>">
<link rel="manifest" href="<?= APP_URL ?>/manifest.json">
<meta name="theme-color" content="#e91e8c">
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type'    => 'Organization',
    'name'     => \App\Frontend\Services\SettingsService::get('site_name','Namma E Store'),
    'url'      => APP_URL,
], JSON_UNESCAPED_SLASHES) ?>
</script>
<?= $structuredData ?? '' ?>
</head>
<body data-app-url="<?= APP_URL ?>" data-csrf-token="<?= csrf_token() ?>">
<?php
$siteName = \App\Frontend\Services\SettingsService::get('site_name','Namma E Store');
$isLogged = \App\Core\Auth::isUserLoggedIn();
$user     = \App\Core\Auth::user();
$cats     = $categories ?? [];
$cartCnt  = $cartCount ?? 0;
?>

<!-- FLASH TICKER -->
<div class="flash-bar">
  <div class="flash-ticker" id="ticker">
    <span>Free delivery on orders above ₹499</span>
    <span>Upto 70% off on Fashion</span>
    <span><a href="<?= APP_URL ?>/sell">New sellers joining daily – Sell on <?= e($siteName) ?></a></span>
    <span>EMI available on products above ₹3000</span>
    <span>Flash Sale: Extra 15% off with code BAZAR15</span>
    <span>Free delivery on orders above ₹499</span>
    <span>Upto 70% off on Fashion</span>
    <span><a href="<?= APP_URL ?>/sell">New sellers joining daily – Sell on <?= e($siteName) ?></a></span>
    <span>EMI available on products above ₹3000</span>
    <span>Flash Sale: Extra 15% off with code BAZAR15</span>
  </div>
</div>

<!-- STICKY HEADER -->
<header class="header">
  <div class="header-top">
    <a href="<?= APP_URL ?>" class="logo">Namma <span>E</span> Store</a>
    <div class="header-search">
      <form action="<?= APP_URL ?>/search" method="GET">
        <select name="category"><option value="">All</option><?php foreach($cats as $cat): ?><option value="<?= $cat['id'] ?>" <?= (($filters['category']??$_GET['category']??'')==$cat['id'])?'selected':'' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select>
        <input type="text" name="q" placeholder="Search products, brands, sellers..." value="<?= e($_GET['q']??'') ?>">
        <button type="submit">🔍</button>
      </form>
    </div>
    <div class="header-actions">
      <a href="<?= APP_URL ?>/sell" class="header-btn sell-header-btn"><span class="icon">🏪</span><span class="label">Sell on <?= e($siteName) ?></span></a>
      <div class="desk-account-wrap">
        <button class="header-btn" data-action="toggle-desk-account"><span class="icon">👤</span><span class="label"><?= $isLogged?e($user['name']):'Account' ?></span></button>
        <div class="desk-account-dropdown" id="deskAccDropdown">
          <div class="desk-acc-header">
            <div class="desk-acc-avatar"><?= $isLogged?strtoupper(substr($user['name'],0,1)):'👤' ?></div>
            <div class="desk-acc-info">
              <div class="acc-name"><?= $isLogged?e($user['name']):'Hello, Guest!' ?></div>
              <div class="acc-email"><?= $isLogged?e($user['email']):'Login to your account' ?></div>
            </div>
          </div>
          <ul class="desk-acc-list">
            <?php if($isLogged): ?>
            <li><a href="<?= APP_URL ?>/account">👤 My Account</a></li>
            <li><a href="<?= APP_URL ?>/account/orders">📦 My Orders</a></li>
            <li><a href="<?= APP_URL ?>/account/notifications">🔔 Notifications</a></li>
            <li><a href="<?= APP_URL ?>/account/wishlist">❤️ Wishlist</a></li>
            <li><a href="<?= APP_URL ?>/account/addresses">📍 Addresses</a></li>
            <li><a href="<?= APP_URL ?>/account/payments">💳 Payments</a></li>
            <li><a href="<?= APP_URL ?>/track">🔍 Track Order</a></li>
            <li><a href="<?= APP_URL ?>/logout" class="logout-link">🚪 Logout</a></li>
            <?php else: ?>
            <li><a href="<?= APP_URL ?>/login">🔐 Login</a></li>
            <li><a href="<?= APP_URL ?>/register">✏️ Register</a></li>
            <li><a href="<?= APP_URL ?>/track">🔍 Track Order</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
      <div class="btn-wrap" data-action="open-cart">
        <button class="header-btn"><span class="icon">🛒</span><span class="label">Cart</span></button>
        <span class="badge" id="cartBadgeTop"><?= $cartCnt > 0 ? $cartCnt : '' ?></span>
      </div>
    </div>
  </div>
  <nav class="header-nav">
    <div class="header-nav-inner">
      <a href="<?= APP_URL ?>" class="nav-link">🏠 Home</a>
      <?php foreach($cats as $cat): ?><a href="<?= APP_URL ?>/category/<?= e($cat['slug']) ?>" class="nav-link"><?= e($cat['name']) ?></a><?php endforeach; ?>
      <a href="<?= APP_URL ?>/products?sort=popular" class="nav-link">🔥 Trending</a>
      <a href="<?= APP_URL ?>/track" class="nav-link">📦 Track Order</a>
    </div>
  </nav>
</header>

<!-- FLASH MESSAGE -->
<?php if(!empty($_SESSION['flash'])): $fl=$_SESSION['flash']; unset($_SESSION['flash']); ?>
<div class="flash-msg"><div class="alert alert-<?= e($fl['type']) ?>"><?= e($fl['message']) ?></div></div>
<?php endif; ?>

<!-- PAGE CONTENT -->
<?= $content ?>

<!-- CART DRAWER -->
<div class="cart-overlay" id="cartOverlay" data-action="close-cart"></div>
<div class="cart-drawer" id="cartDrawer">
  <div class="cart-header"><h3>🛒 Your Cart (<span id="cartCount"><?= $cartCnt ?></span>)</h3><button class="cart-close" data-action="close-cart">✕</button></div>
  <div class="cart-body" id="cartBody"><div class="cart-empty"><div class="ce-icon">🛒</div><p>Your cart is empty</p><a href="<?= APP_URL ?>/products" class="btn-shop" data-action="close-cart">Start Shopping →</a></div></div>
  <div class="cart-footer is-hidden" id="cartFooter">
    <div class="free-ship" id="freeShipMsg"></div>
    <div class="cart-total"><span class="label">Total</span><span class="amount" id="cartTotal">₹0</span></div>
    <a href="<?= APP_URL ?>/checkout" class="checkout-btn">Proceed to Checkout →</a>
  </div>
</div>

<!-- MOBILE OVERLAY -->
<div class="mob-overlay" id="mobOverlay" data-action="close-all-popups"></div>

<!-- MOBILE CATEGORIES POPUP -->
<div class="mob-popup" id="mobCatsPopup">
  <div class="mob-pop-title">🗂️ Categories<button data-action="close-all-popups">✕</button></div>
  <div class="mob-cat-grid">
    <?php foreach($cats as $cat): ?><a href="<?= APP_URL ?>/category/<?= e($cat['slug']) ?>" class="mob-cat-item" data-action="close-all-popups"><span class="c-icon">🏷️</span><span class="c-name"><?= e($cat['name']) ?></span></a><?php endforeach; ?>
    <a href="<?= APP_URL ?>/products" class="mob-cat-item" data-action="close-all-popups"><span class="c-icon">🛍️</span><span class="c-name">All</span></a>
  </div>
</div>

<!-- MOBILE ACCOUNT POPUP -->
<div class="mob-popup" id="mobAccPopup">
  <div class="mob-pop-title">👤 <?= $isLogged?e($user['name']):'My Account' ?><button data-action="close-all-popups">✕</button></div>
  <ul class="mob-acc-list">
    <?php if($isLogged): ?>
    <li><a href="<?= APP_URL ?>/account" data-action="close-all-popups"><span class="da-icon">👤</span> My Account</a></li>
    <li><a href="<?= APP_URL ?>/account/orders" data-action="close-all-popups"><span class="da-icon">📦</span> My Orders</a></li>
    <li><a href="<?= APP_URL ?>/account/wishlist" data-action="close-all-popups"><span class="da-icon">❤️</span> Wishlist</a></li>
    <li><a href="<?= APP_URL ?>/track" data-action="close-all-popups"><span class="da-icon">🔍</span> Track Order</a></li>
    <li><a href="<?= APP_URL ?>/info" data-action="close-all-popups"><span class="da-icon">ℹ️</span> Info Center</a></li>
    <li><a href="<?= APP_URL ?>/sell" data-action="close-all-popups"><span class="da-icon">🏪</span> Sell on <?= e($siteName) ?></a></li>
    <li><a href="<?= APP_URL ?>/logout" data-action="close-all-popups"><span class="da-icon">🚪</span> Logout</a></li>
    <?php else: ?>
    <li><a href="<?= APP_URL ?>/login" data-action="close-all-popups"><span class="da-icon">🔐</span> Login</a></li>
    <li><a href="<?= APP_URL ?>/register" data-action="close-all-popups"><span class="da-icon">✏️</span> Register</a></li>
    <li><a href="<?= APP_URL ?>/track" data-action="close-all-popups"><span class="da-icon">🔍</span> Track Order</a></li>
    <li><a href="<?= APP_URL ?>/info" data-action="close-all-popups"><span class="da-icon">ℹ️</span> Info Center</a></li>
    <li><a href="<?= APP_URL ?>/sell" data-action="close-all-popups"><span class="da-icon">🏪</span> Sell on <?= e($siteName) ?></a></li>
    <?php endif; ?>
  </ul>
</div>

<!-- TOAST -->
<div class="toast" id="toast"></div>
<button class="back-top" id="backTop" data-action="scroll-top">↑</button>

<!-- MOBILE BOTTOM NAV -->
<nav class="mobile-nav">
  <div class="mobile-nav-inner">
    <a href="<?= APP_URL ?>" class="mob-nav-btn nav-home"><span class="m-icon">🏠</span><span class="m-label">Home</span></a>
    <button class="mob-nav-btn nav-cats" data-action="toggle-cats"><span class="m-icon">🗂️</span><span class="m-label">Categories</span></button>
    <div class="mob-nav-center-wrap" data-action="open-cart"><div class="mob-nav-center">🛒<span class="mob-cart-badge" id="cartBadgeMob"><?= $cartCnt ?: '' ?></span></div><span class="mob-nav-center-label">Cart</span></div>
    <button class="mob-nav-btn" data-action="toggle-account"><span class="m-icon">👤</span><span class="m-label">Account</span></button>
    <button class="mob-nav-btn" data-action="toggle-menu"><span class="m-icon">☰</span><span class="m-label">Menu</span></button>
  </div>
</nav>

<!-- MOBILE SLIDE MENU (replaces the top nav on mobile) -->
<div class="mob-menu-drawer" id="mobMenuDrawer">
  <div class="mmd-head">
    <span class="mmd-logo">Namma <span>E</span> Store</span>
    <button data-action="close-all-popups">✕</button>
  </div>
  <nav class="mmd-links">
    <a href="<?= APP_URL ?>" data-action="close-all-popups"><span class="mmd-ic">🏠</span> Home</a>
    <div class="mmd-sec">Categories</div>
    <?php foreach($cats as $cat): ?>
    <a href="<?= APP_URL ?>/category/<?= e($cat['slug']) ?>" data-action="close-all-popups"><span class="mmd-ic">🏷️</span> <?= e($cat['name']) ?></a>
    <?php endforeach; ?>
    <div class="mmd-sec">Explore</div>
    <a href="<?= APP_URL ?>/products?sort=popular" data-action="close-all-popups"><span class="mmd-ic">🔥</span> Trending</a>
    <a href="<?= APP_URL ?>/products" data-action="close-all-popups"><span class="mmd-ic">🛍️</span> All Products</a>
    <a href="<?= APP_URL ?>/track" data-action="close-all-popups"><span class="mmd-ic">📦</span> Track Order</a>
    <a href="<?= APP_URL ?>/info" data-action="close-all-popups"><span class="mmd-ic">ℹ️</span> Info Center</a>
    <div class="mmd-sec">Sellers</div>
    <a href="<?= APP_URL ?>/sell" data-action="close-all-popups"><span class="mmd-ic">🏪</span> Sell on <?= e($siteName) ?></a>
  </nav>
</div>

<!-- FOOTER -->
<footer class="footer">
  <nav class="footer-links">
    <a href="<?= APP_URL ?>/sell">Sell on <?= e($siteName) ?></a>
    <?php foreach (\App\Frontend\Services\PageService::all() as $pgSlug => $pg): ?>
    <a href="<?= APP_URL ?>/info/<?= e($pgSlug) ?>"><?= e($pg[1]) ?></a>
    <?php endforeach; ?>
  </nav>
  <div class="footer-bottom"><span>&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved.</span></div>
</footer>

<script src="<?= asset('frontend/js/form-validation.js') ?>"></script>
<script src="<?= asset('frontend/js/main.js') ?>"></script>
<?= $scripts ?? '' ?>
</body>
</html>
