<?php
$allPages  = \App\Frontend\Services\PageService::all();
$pageKeys  = array_keys($allPages);
$idx       = array_search($pageSlug, $pageKeys, true);
$prevSlug  = $idx !== false && $idx > 0 ? $pageKeys[$idx - 1] : null;
$nextSlug  = $idx !== false && $idx < count($pageKeys) - 1 ? $pageKeys[$idx + 1] : null;
?>
<div class="info-hero-banner slim">
  <div class="info-hero-inner">
    <nav class="breadcrumbs light" aria-label="Breadcrumb">
      <a href="<?= APP_URL ?>">Home</a>
      <span class="bc-sep">›</span>
      <a href="<?= APP_URL ?>/info">Info Center</a>
      <span class="bc-sep">›</span>
      <span class="bc-current"><?= e($pageTitle) ?></span>
    </nav>
    <h1><span class="ih-emoji"><?= $pageIcon ?></span> <?= e($pageTitle) ?></h1>
  </div>
</div>

<div class="container info-container">
  <article class="page-article">
    <?php if (!empty($pageContent)): ?>
      <?= $pageContent /* admin-authored HTML from mc_pages, tokens already rendered */ ?>
    <?php elseif (!empty($contentView)): ?>
      <?php include $contentView; ?>
    <?php endif; ?>
    <?php if ($pageSlug === 'contact-us') include __DIR__ . '/partials/contact-block.php'; ?>
  </article>

  <nav class="info-page-nav">
    <?php if ($prevSlug): ?>
    <a href="<?= APP_URL ?>/info/<?= e($prevSlug) ?>" class="ipn-link prev"><span class="ipn-dir">← Previous</span><span class="ipn-title"><?= $allPages[$prevSlug][0] ?> <?= e($allPages[$prevSlug][1]) ?></span></a>
    <?php else: ?><span></span><?php endif; ?>
    <a href="<?= APP_URL ?>/info" class="ipn-link center"><span class="ipn-dir">ℹ️</span><span class="ipn-title">Info Center</span></a>
    <?php if ($nextSlug): ?>
    <a href="<?= APP_URL ?>/info/<?= e($nextSlug) ?>" class="ipn-link next"><span class="ipn-dir">Next →</span><span class="ipn-title"><?= $allPages[$nextSlug][0] ?> <?= e($allPages[$nextSlug][1]) ?></span></a>
    <?php else: ?><span></span><?php endif; ?>
  </nav>
</div>
