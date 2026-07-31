<div class="info-hero-banner">
  <div class="info-hero-inner">
    <nav class="breadcrumbs light" aria-label="Breadcrumb">
      <a href="<?= APP_URL ?>">Home</a>
      <span class="bc-sep">›</span>
      <span class="bc-current">Info Center</span>
    </nav>
    <h1><span class="ih-emoji">ℹ️</span> Info Center</h1>
    <p>Everything you need to know about shopping with <?= e(\App\Frontend\Services\SettingsService::get('site_name','Namma E Store')) ?></p>
  </div>
  <div class="ih-deco">🛍️</div>
</div>

<div class="container info-container">
  <div class="info-grid">
    <?php foreach ($pages as $slug => [$icon, $pTitle, $pSub]): ?>
    <a href="<?= APP_URL ?>/info/<?= e($slug) ?>" class="info-card">
      <span class="ic-icon"><span><?= $icon ?></span></span>
      <span class="ic-title"><?= e($pTitle) ?></span>
      <span class="ic-sub"><?= e($pSub) ?></span>
      <span class="ic-go">Read more <span class="ic-arrow">→</span></span>
    </a>
    <?php endforeach; ?>
  </div>

  <div class="info-help-strip">
    <div class="ihs-text"><strong>Still need help?</strong> Our support team replies within 24 hours.</div>
    <a href="<?= APP_URL ?>/info/contact-us" class="ihs-btn">📞 Contact Us</a>
  </div>
</div>
