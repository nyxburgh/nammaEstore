<link rel="stylesheet" href="<?= asset('frontend/css/store.css') ?>">

<!-- STORE HERO BANNER -->
<div class="store-hero">
  <div class="store-hero-inner">
    <div class="store-logo">
      <?php if(!empty($shop['shop_logo'])): ?>
        <img src="<?= UPLOAD_URL ?>/<?= e($shop['shop_logo']) ?>" alt="<?= e($shop['shop_name']) ?>">
      <?php else: ?>
        <?= strtoupper(substr($shop['shop_name'], 0, 1)) ?>
      <?php endif; ?>
    </div>
    <div class="store-info">
      <h1>🏪 <?= e($shop['shop_name']) ?></h1>
      <?php if(!empty($shop['description'])): ?>
        <p><?= e($shop['description']) ?></p>
      <?php endif; ?>
      <div class="store-meta">
        <span>🛍️ <?= number_format($products['total']) ?> product<?= $products['total'] != 1 ? 's' : '' ?></span>
        <?php if(!empty($shop['city'])): ?><span>📍 <?= e($shop['city'].', '.$shop['state']) ?></span><?php endif; ?>
        <?php if(!empty($shop['gst_number'])): ?><span>✅ GST Verified</span><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
// Build base URL for this store
$storeBase = VENDOR_STORE_MODE === 'path'
    ? APP_URL . '/' . VENDOR_STORE_BASE . '/' . e($shop['shop_slug'])
    : APP_URL . '/' . e($shop['shop_slug']);
?>

<div class="container shop-container">
  <div class="shop-layout">

    <!-- SIDEBAR FILTERS -->
    <aside class="filter-sidebar">
      <div class="fs-title">🔍 Filter Products</div>
      <form method="GET" onsubmit="return validateForm(this)">
        <div class="filter-group">
          <h4>Category</h4>
          <a href="<?= $storeBase ?>" class="filter-chip <?= empty($filters['category']) ? 'active' : '' ?>">All</a>
          <?php foreach($categories as $cat): ?>
            <a href="<?= $storeBase ?>?category=<?= $cat['id'] ?>" class="filter-chip <?= ($filters['category'] ?? '') == $cat['id'] ? 'active' : '' ?>"><?= e($cat['name']) ?></a>
          <?php endforeach; ?>
        </div>
        <div class="filter-group">
          <h4>Price Range</h4>
          <div class="price-row">
            <input type="number" name="min_price" id="minPrice" placeholder="₹ Min" value="<?= e($filters['min_price'] ?? '') ?>" min="0" oninput="validateField(this)" onblur="validateField(this)">
            <span>to</span>
            <input type="number" name="max_price" id="maxPrice" placeholder="₹ Max" value="<?= e($filters['max_price'] ?? '') ?>" min="0" data-gte-field="#minPrice" oninput="validateField(this)" onblur="validateField(this)">
          </div>
        </div>
        <div class="filter-group">
          <h4>Sort By</h4>
          <?php foreach(['newest' => 'Newest', 'popular' => 'Popular', 'price_asc' => 'Price ↑', 'price_desc' => 'Price ↓'] as $v => $l): ?>
          <label class="sort-radio">
            <input type="radio" name="sort" value="<?= $v ?>" <?= ($filters['sort'] ?? 'newest') === $v ? 'checked' : '' ?>>
            <?= $l ?>
          </label>
          <?php endforeach; ?>
        </div>
        <input type="hidden" name="vendor_id" value="<?= (int)$shop['user_id'] ?>">
        <button type="submit" class="btn-apply">Apply Filters</button>
        <a href="<?= $storeBase ?>" class="filter-reset">Reset</a>
      </form>
    </aside>

    <!-- PRODUCTS -->
    <div>
      <div class="sort-bar">
        <div class="result-count">
          <strong><?= number_format($products['total']) ?></strong> product<?= $products['total'] != 1 ? 's' : '' ?> from <strong><?= e($shop['shop_name']) ?></strong>
        </div>
        <form method="GET" class="sort-form">
          <?php if(!empty($filters['min_price'])): ?><input type="hidden" name="min_price" value="<?= e($filters['min_price']) ?>"><?php endif; ?>
          <?php if(!empty($filters['max_price'])): ?><input type="hidden" name="max_price" value="<?= e($filters['max_price']) ?>"><?php endif; ?>
          <?php if(!empty($filters['category'])): ?><input type="hidden" name="category" value="<?= e($filters['category']) ?>"><?php endif; ?>
          <input type="hidden" name="vendor_id" value="<?= (int)$shop['user_id'] ?>">
          <select name="sort" class="sort-select" data-action="submit-on-change">
            <?php foreach(['newest' => 'Newest', 'popular' => 'Popular', 'price_asc' => 'Price ↑', 'price_desc' => 'Price ↓'] as $v => $l): ?>
            <option value="<?= $v ?>" <?= ($filters['sort'] ?? 'newest') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </form>
      </div>

      <?php if(empty($products['data'])): ?>
      <div class="empty-state">
        <div class="es-icon">🛍️</div>
        <h3>No products yet</h3>
        <p>This shop hasn't listed any products matching your filters.</p>
      </div>
      <?php else: ?>
      <div class="products-grid">
        <?php foreach($products['data'] as $p):
          $price = (float)($p['sale_price'] ?: $p['price']);
          $orig  = (float)$p['price'];
          $disc  = $orig > 0 && $price < $orig ? round((1 - $price / $orig) * 100) . '% off' : '';
          $imgHtml = !empty($p['image'])
            ? '<img src="' . UPLOAD_URL . '/' . $p['image'] . '" alt="' . e($p['name']) . '">'
            : '<span class="ph-lg">🛍️</span>';
        ?>
        <div class="product-card" data-action="go" data-href="<?= APP_URL ?>/product/<?= e($p['slug']) ?>">
          <div class="product-img">
            <?= $imgHtml ?>
            <?php if($disc): ?><span class="product-discount-badge"><?= $disc ?></span><?php endif; ?>
            <button class="product-wishlist" data-action="toggle-wishlist" data-product-id="<?= (int)$p['id'] ?>">🤍</button>
          </div>
          <div class="product-info">
            <div class="product-name"><?= e($p['name']) ?></div>
            <div class="product-price">
              <span class="price-current"><?= currency($price) ?></span>
              <?php if($disc): ?><span class="price-original"><?= currency($orig) ?></span><?php endif; ?>
              <span class="tax-tag">incl. taxes</span>
            </div>
            <button class="product-add-btn" data-action="add-to-cart" data-product-id="<?= (int)$p['id'] ?>">+ Add to Cart</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- PAGINATION -->
      <?= productPagination($products, parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)) ?>
      <?php endif; ?>
    </div>

  </div>
</div>
