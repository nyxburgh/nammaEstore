<link rel="stylesheet" href="<?= asset('frontend/css/shop.css') ?>">

<!-- PAGE HERO -->
<div class="page-hero">
  <div class="page-hero-inner">
    <h1><?= isset($category) ? e($category['name']) : (isset($searchQ) ? 'Search: "'.e($searchQ).'"' : 'All Products') ?></h1>
    <div class="breadcrumb">
      <a href="<?= APP_URL ?>">Home</a><span class="sep">›</span>
      <?php if(isset($category)): ?><a href="<?= APP_URL ?>/products">Products</a><span class="sep">›</span><span><?= e($category['name']) ?></span>
      <?php elseif(isset($searchQ)): ?><span>Search Results</span>
      <?php else: ?><span>All Products</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="container shop-container">
  <div class="shop-layout">
    <!-- FILTER SIDEBAR -->
    <aside class="filter-sidebar">
      <div class="fs-title">🔍 Filters</div>
      <form method="GET" action="" onsubmit="return validateForm(this)">
        <?php if(isset($searchQ)): ?><input type="hidden" name="q" value="<?= e($searchQ) ?>"><?php endif; ?>
        <div class="filter-group">
          <h4>Categories</h4>
          <div><?php foreach($categories as $cat): ?>
            <a href="<?= APP_URL ?>/category/<?= e($cat['slug']) ?>" class="filter-chip <?= isset($category)&&$category['slug']===$cat['slug']?'active':'' ?>"><?= e($cat['name']) ?></a>
          <?php endforeach; ?></div>
        </div>
        <div class="filter-group">
          <h4>Price Range</h4>
          <div class="price-inputs">
            <input type="number" name="min_price" id="minPrice" placeholder="₹ Min" value="<?= e($filters['min_price']??'') ?>" min="0" oninput="validateField(this)" onblur="validateField(this)">
            <span>to</span>
            <input type="number" name="max_price" id="maxPrice" placeholder="₹ Max" value="<?= e($filters['max_price']??'') ?>" min="0" data-gte-field="#minPrice" oninput="validateField(this)" onblur="validateField(this)">
          </div>
        </div>
        <div class="filter-group">
          <h4>Sort By</h4>
          <?php foreach(['newest'=>'Newest First','popular'=>'Most Popular','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low'] as $val=>$lbl): ?>
          <label class="sort-radio">
            <input type="radio" name="sort" value="<?= $val ?>" <?= ($filters['sort']??'newest')===$val?'checked':'' ?>>
            <?= $lbl ?>
          </label>
          <?php endforeach; ?>
        </div>
        <button type="submit" class="btn-filter">Apply Filters</button>
        <a href="<?= isset($category)?APP_URL.'/category/'.e($category['slug']):APP_URL.'/products' ?>" class="filter-reset">Reset</a>
      </form>
    </aside>

    <!-- PRODUCTS -->
    <div>
      <div class="sort-bar">
        <div class="result-count">Showing <strong><?= $products['from'] ?>–<?= $products['to'] ?></strong> of <strong><?= number_format($products['total']) ?></strong> products</div>
        <div class="sort-right">
          <form method="GET" class="sort-form">
            <?php if(isset($searchQ)): ?><input type="hidden" name="q" value="<?= e($searchQ) ?>"><?php endif; ?>
            <?php if(isset($category)): ?><?php endif; ?>
            <?php if(!empty($filters['min_price'])): ?><input type="hidden" name="min_price" value="<?= e($filters['min_price']) ?>"><?php endif; ?>
            <?php if(!empty($filters['max_price'])): ?><input type="hidden" name="max_price" value="<?= e($filters['max_price']) ?>"><?php endif; ?>
            <select name="sort" class="sort-select" data-action="submit-on-change">
              <?php foreach(['newest'=>'Newest','popular'=>'Popular','price_asc'=>'Price ↑','price_desc'=>'Price ↓'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= ($filters['sort']??'newest')===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>

      <div class="products-grid">
        <?php if(empty($products['data'])): ?>
        <div class="empty-products">
          <div class="ep-icon">🔍</div>
          <h3>No products found</h3>
          <p>Try changing your filters or search query.</p>
          <a href="<?= APP_URL ?>/products" class="btn-browse">Browse All Products</a>
        </div>
        <?php else: foreach($products['data'] as $p):
          $price = (float)($p['sale_price'] ?: $p['price']);
          $orig  = (float)$p['price'];
          $disc  = $orig > 0 && $price < $orig ? round((1-$price/$orig)*100).'% off' : '';
          $imgHtml = !empty($p['image']) ? '<img src="'.UPLOAD_URL.'/'.$p['image'].'" alt="'.e($p['name']).'">' : '<span class="ph-lg">🛍️</span>';
        ?>
        <div class="product-card" data-action="go" data-href="<?= APP_URL ?>/product/<?= e($p['slug']) ?>">
          <div class="product-img"><?= $imgHtml ?>
            <?php if($disc): ?><span class="product-discount-badge"><?= $disc ?></span><?php endif; ?>
            <button class="product-wishlist" data-action="toggle-wishlist" data-product-id="<?= (int)$p['id'] ?>">🤍</button>
          </div>
          <div class="product-info">
            <div class="seller-tag">🏪 <?= e($p['shop_name']??'Shop') ?></div>
            <div class="product-name"><?= e($p['name']) ?></div>
            <div class="product-price">
              <span class="price-current"><?= currency($price) ?></span>
              <?php if($disc): ?><span class="price-original"><?= currency($orig) ?></span><?php endif; ?>
              <span class="tax-tag">incl. taxes</span>
            </div>
            <button class="product-add-btn" data-action="add-to-cart" data-product-id="<?= (int)$p['id'] ?>">+ Add to Cart</button>
          </div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- PAGINATION -->
      <?= productPagination($products, parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH)) ?>
    </div>
  </div>
</div>
