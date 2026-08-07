<?php
// Shared product card helper
function productCard(array $p): string {
    $price     = (float)($p['sale_price'] ?: $p['price']);
    $origPrice = (float)$p['price'];
    $disc      = $origPrice > 0 && $price < $origPrice ? round((1-$price/$origPrice)*100).'% off' : '';
    $imgHtml   = !empty($p['image'])
        ? '<img src="'.UPLOAD_URL.'/'.$p['image'].'" alt="'.e($p['name']).'">'
        : '<span class="ph-lg">🛍️</span>';
    return '<div class="product-card" data-action="go" data-href="'.APP_URL.'/product/'.e($p['slug']).'">'
        .'<div class="product-img">'.$imgHtml
        .($disc?'<span class="product-discount-badge">'.$disc.'</span>':'')
        .'<button class="product-wishlist" data-action="toggle-wishlist" data-product-id="'.(int)$p['id'].'">🤍</button>'
        .'</div>'
        .'<div class="product-info">'
        .'<div class="seller-tag">🏪 '.e($p['shop_name']??'Shop').'</div>'
        .'<div class="product-name">'.e($p['name']).'</div>'
        .'<div class="product-price"><span class="price-current">'.currency($price).'</span>'
        .($disc?'<span class="price-original">'.currency($origPrice).'</span>':'')
        .'<span class="tax-tag">incl. taxes</span>'
        .'</div>'
        .'<button class="product-add-btn" data-action="add-to-cart" data-product-id="'.(int)$p['id'].'">+ Add to Cart</button>'
        .'</div></div>';
}
?>
<link rel="stylesheet" href="<?= asset('frontend/css/home.css') ?>">

<!-- HERO BANNER -->
<section class="hero-sec">
  <div class="hero">
    <div class="hero-slides" id="heroSlides">
      <div class="hero-slide hero-slide-1">
        <div class="hero-content">
          <div class="hero-badge pulse">🔥 MEGA SALE</div>
          <h1 class="hero-title">Style That<br>Speaks Volumes</h1>
          <p class="hero-sub">Upto 70% off on top fashion brands</p>
          <a href="<?= APP_URL ?>/category/fashion" class="hero-cta">Shop Fashion ›</a>
        </div>
        <div class="hero-deco">👗</div>
      </div>
      <div class="hero-slide hero-slide-2">
        <div class="hero-content">
          <div class="hero-badge blue">⚡ NEW ARRIVALS</div>
          <h1 class="hero-title">Tech That<br>Changes Life</h1>
          <p class="hero-sub">Latest gadgets at unbeatable prices</p>
          <a href="<?= APP_URL ?>/category/electronics" class="hero-cta blue">Shop Electronics ›</a>
        </div>
        <div class="hero-deco">📱</div>
      </div>
      <div class="hero-slide hero-slide-3">
        <div class="hero-content">
          <div class="hero-badge light">🏠 HOME DECOR</div>
          <h1 class="hero-title">Make Your<br>Home Beautiful</h1>
          <p class="hero-sub">Transform your space with premium decor</p>
          <a href="<?= APP_URL ?>/category/home-kitchen" class="hero-cta purple">Shop Home ›</a>
        </div>
        <div class="hero-deco">🛋️</div>
      </div>
    </div>
    <div class="hero-dots">
      <div class="hero-dot active" data-action="go-slide" data-slide="0"></div>
      <div class="hero-dot" data-action="go-slide" data-slide="1"></div>
      <div class="hero-dot" data-action="go-slide" data-slide="2"></div>
    </div>
  </div>
</section>

<div class="container home-container">
  <!-- FEATURES STRIP -->
  <div class="features-strip reveal">
    <?php foreach([['🚚','Free Delivery','On orders above ₹499'],['🔄','Easy Returns','7-day hassle-free'],['🔒','Secure Payment','100% encrypted'],['💬','24/7 Support','Always here to help']] as [$fIcon,$fTitle,$fSub]): ?>
    <div class="feature-item"><div class="feature-icon"><?= $fIcon ?></div><div><div class="f-title"><?= $fTitle ?></div><div class="f-sub"><?= $fSub ?></div></div></div>
    <?php endforeach; ?>
  </div>

  <!-- SHOP BY CATEGORY -->
  <section class="reveal home-sec">
    <div class="section-header"><h2 class="section-title">Shop by Category</h2><a href="<?= APP_URL ?>/products" class="view-all">View All →</a></div>
    <div class="cat-grid">
      <?php $catIcons=['electronics'=>'📱','fashion'=>'👗','home-kitchen'=>'🏠','sports'=>'🏃','books'=>'📚','beauty'=>'💄','grocery'=>'🍎','gaming'=>'🎮'];
      foreach($categories as $cat): ?>
      <a href="<?= APP_URL ?>/category/<?= e($cat['slug']) ?>" class="cat-card">
        <div class="cat-icon">
          <?php if(!empty($cat['image'])): ?><img src="<?= UPLOAD_URL.'/'.e($cat['image']) ?>" alt="<?= e($cat['name']) ?>">
          <?php else: ?><?= $catIcons[$cat['slug']] ?? '🛍️' ?><?php endif; ?>
        </div>
        <span class="cat-name"><?= e($cat['name']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- PROMO STRIP -->
  <section class="reveal home-sec">
    <div class="promo-strip">
      <div class="promo-card promo-card-1" data-action="go" data-href="<?= APP_URL ?>/category/fashion"><div class="promo-text"><div class="label">Summer Sale</div><h3>Flat 40% off<br>on Fashion</h3><p>Limited time offer</p></div><div class="promo-emoji">👗</div></div>
      <div class="promo-card promo-card-2" data-action="go" data-href="<?= APP_URL ?>/category/electronics"><div class="promo-text"><div class="label">Tech Week</div><h3>New Gadgets<br>Starting ₹999</h3><p>Top brands, best deals</p></div><div class="promo-emoji">💻</div></div>
      <div class="promo-card promo-card-3" data-action="go" data-href="<?= APP_URL ?>/products"><div class="promo-text"><div class="label">Seller Offer</div><h3>Sell & Earn<br>Zero Commission</h3><p>First ₹20,000 free</p></div><div class="promo-emoji">🏪</div></div>
    </div>
  </section>

  <!-- FLASH SALE -->
  <section class="reveal home-sec">
    <div class="flash-header">
      <h2>⚡ Flash Sale</h2>
      <div class="countdown"><div class="count-box" id="ch">04</div><span class="count-sep">:</span><div class="count-box" id="cm">27</div><span class="count-sep">:</span><div class="count-box" id="cs">45</div></div>
      <a href="<?= APP_URL ?>/products?sort=popular">View All →</a>
    </div>
    <div class="products-grid" id="flashProducts">
      <?php foreach(array_slice($trending, 0, 6) as $p): echo productCard($p); endforeach; ?>
      <?php if(empty($trending)): ?>
      <?php for($i=0;$i<4;$i++): ?>
      <div class="product-card sk-card"><div class="product-img sk-img"></div><div class="product-info"><div class="sk-line"></div><div class="sk-line short"></div></div></div>
      <?php endfor; endif; ?>
    </div>
  </section>

  <!-- TRENDING -->
  <section class="reveal home-sec">
    <div class="section-header"><h2 class="section-title">🔥 Trending Now</h2><a href="<?= APP_URL ?>/products?sort=popular" class="view-all">View All →</a></div>
    <div class="products-grid" id="trendingProducts">
      <?php foreach($trending as $p): echo productCard($p); endforeach; ?>
      <?php if(empty($trending)): ?><div class="grid-empty">No products yet — check back soon!</div><?php endif; ?>
    </div>
  </section>

  <!-- DEALS OF THE DAY -->
  <?php if(!empty($deals)): ?>
  <section class="reveal home-sec">
    <div class="section-header"><h2 class="section-title">🤑 Deals of the Day</h2><a href="<?= APP_URL ?>/products?sort=popular" class="view-all">View All →</a></div>
    <div class="deals-grid">
      <?php foreach($deals as $d):
        $price = (float)($d['sale_price'] ?: $d['price']);
        $orig  = (float)$d['price'];
        $disc  = $orig > 0 ? round((1-$price/$orig)*100) : 0;
        $imgHtml = !empty($d['image']) ? '<img src="'.UPLOAD_URL.'/'.$d['image'].'" alt="'.e($d['name']).'">' : '<span class="ph-xl">🛍️</span>';
      ?>
      <div class="deal-card" data-action="go" data-href="<?= APP_URL ?>/product/<?= e($d['slug']) ?>">
        <div class="deal-img"><?= $imgHtml ?><span class="deal-pct"><?= $disc ?>% OFF</span></div>
        <div class="deal-info">
          <h4><?= e($d['name']) ?></h4>
          <div class="deal-progress">
            <div class="progress-label"><span>Stock Limited</span><span>Hurry!</span></div>
            <div class="progress-bar"><div class="progress-fill" data-pct="<?= min(90,max(20,100-$d['stock'])) ?>"></div></div>
          </div>
          <div class="deal-price"><?= currency($price) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- NEW ARRIVALS -->
  <section class="reveal home-sec">
    <div class="section-header"><h2 class="section-title">✨ New Arrivals</h2><a href="<?= APP_URL ?>/products?sort=newest" class="view-all">View All →</a></div>
    <div class="products-grid">
      <?php foreach($newArrivals as $p): echo productCard($p); endforeach; ?>
      <?php if(empty($newArrivals)): ?><div class="grid-empty">No new arrivals yet.</div><?php endif; ?>
    </div>
  </section>

  <!-- NEWSLETTER -->
  <section class="reveal home-sec">
    <div class="newsletter">
      <h2>Stay in the Loop 📬</h2>
      <p>Get the latest deals, new arrivals and exclusive offers — straight to your inbox.</p>
      <div class="newsletter-form">
        <input type="email" placeholder="Enter your email address" aria-label="Email address">
        <button type="button" data-action="subscribe-newsletter">Subscribe →</button>
      </div>
    </div>
  </section>
</div>

<?php $scripts = '<script src="'.asset('frontend/js/home.js').'"></script>'; ?>
