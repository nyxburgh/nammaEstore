<div class="sell-hero">
  <div class="container sell-hero-inner">
    <nav class="breadcrumbs light" aria-label="Breadcrumb">
      <a href="<?= APP_URL ?>">Home</a>
      <span class="bc-sep">›</span>
      <span class="bc-current">Sell on <?= e($siteName) ?></span>
    </nav>
    <h1>Grow your business.<br>Sell on <?= e($siteName) ?>.</h1>
    <p>Join <?= number_format($sellerCount) ?>+ sellers already reaching customers across the country. Set up your store in minutes and start selling today.</p>
    <div class="sell-hero-ctas">
      <a href="<?= SELLER_URL ?>/register" class="sell-btn-primary">Start Selling →</a>
      <a href="<?= SELLER_URL ?>/login" class="sell-btn-secondary">Already a seller? Sign in</a>
    </div>
    <div class="sell-hero-stats">
      <div class="shs-item"><strong><?= number_format($sellerCount) ?>+</strong><span>Active Sellers</span></div>
      <div class="shs-item"><strong><?= number_format($productCount) ?>+</strong><span>Products Listed</span></div>
      <div class="shs-item"><strong>10%</strong><span>Starting Commission</span></div>
    </div>
  </div>
  <div class="sh-deco">🏪</div>
</div>

<div class="container sell-container">

  <!-- WHY SELL -->
  <section class="sell-section">
    <h2 class="sell-section-title">Why sell with us</h2>
    <div class="sell-benefits-grid">
      <div class="sell-benefit-card">
        <span class="sbc-icon">🚀</span>
        <h3>Reach more customers</h3>
        <p>Get discovered by shoppers across every category, with your own storefront page.</p>
      </div>
      <div class="sell-benefit-card">
        <span class="sbc-icon">💸</span>
        <h3>Low commission</h3>
        <p>Keep more of what you earn — plans starting at 0% commission up to a monthly threshold.</p>
      </div>
      <div class="sell-benefit-card">
        <span class="sbc-icon">📊</span>
        <h3>Full seller dashboard</h3>
        <p>Track orders, manage inventory, view earnings and settlements — all in one panel.</p>
      </div>
      <div class="sell-benefit-card">
        <span class="sbc-icon">⚡</span>
        <h3>Fast payouts</h3>
        <p>Seller wallet with a clear settlement and withdrawal history — no surprises.</p>
      </div>
      <div class="sell-benefit-card">
        <span class="sbc-icon">🧾</span>
        <h3>GST-ready invoicing</h3>
        <p>Automatic GST-compliant invoices generated for every order you fulfil.</p>
      </div>
      <div class="sell-benefit-card">
        <span class="sbc-icon">🎯</span>
        <h3>Marketing tools</h3>
        <p>Run coupons, festival offers, and brand promotions to boost your sales.</p>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="sell-section">
    <h2 class="sell-section-title">How it works</h2>
    <div class="sell-steps">
      <div class="sell-step">
        <span class="ss-num">1</span>
        <h3>Create your account</h3>
        <p>Register with your shop name and email, then verify with the OTP we send you.</p>
      </div>
      <div class="sell-step">
        <span class="ss-num">2</span>
        <h3>Set up your store</h3>
        <p>Add your shop details, list your products with photos, pricing and stock.</p>
      </div>
      <div class="sell-step">
        <span class="ss-num">3</span>
        <h3>Get approved</h3>
        <p>Our team reviews your store, usually within one business day.</p>
      </div>
      <div class="sell-step">
        <span class="ss-num">4</span>
        <h3>Start selling</h3>
        <p>Go live, receive orders, and track your earnings from the seller dashboard.</p>
      </div>
    </div>
  </section>

  <!-- PLANS -->
  <?php if (!empty($plans)): ?>
  <section class="sell-section">
    <h2 class="sell-section-title">Choose a plan that fits</h2>
    <div class="sell-plans-grid">
      <?php foreach ($plans as $plan): ?>
      <div class="sell-plan-card">
        <h3><?= e($plan['name']) ?></h3>
        <div class="spc-price">
          <?= $plan['price'] > 0 ? '₹' . number_format($plan['price'], 0) : 'Free' ?>
          <?php if ($plan['price'] > 0): ?><span>/ <?= e($plan['billing_cycle']) ?></span><?php endif; ?>
        </div>
        <p class="spc-desc"><?= e($plan['description']) ?></p>
        <ul class="spc-list">
          <?php if ((float)$plan['free_threshold'] > 0): ?>
          <li>✓ 0% commission up to ₹<?= number_format($plan['free_threshold'], 0) ?>/month</li>
          <li>✓ <?= number_format($plan['commission_after_threshold'], 1) ?>% commission after</li>
          <?php else: ?>
          <li>✓ <?= number_format($plan['commission_pct'], 1) ?>% commission on all sales</li>
          <?php endif; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- CTA STRIP -->
  <div class="sell-cta-strip">
    <div class="scs-text"><strong>Ready to get started?</strong> Create your seller account in under 5 minutes.</div>
    <a href="<?= SELLER_URL ?>/register" class="scs-btn">Register as a Seller →</a>
  </div>

</div>
