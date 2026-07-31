<?php include __DIR__.'/_sidebar.php'; ?>
<div class="card">
  <div class="card-head"><span class="card-title">❤️ My Wishlist</span><span class="head-count"><?= $items['total'] ?> item<?= $items['total']!=1?'s':'' ?></span></div>
  <div class="card-body">
    <?php if(empty($items['data'])): ?>
    <div class="acc-empty"><div class="ae-icon">❤️</div><p>Your wishlist is empty.</p><a href="<?= APP_URL ?>/products" class="ae-btn">Explore Products</a></div>
    <?php else: ?>
    <div class="wish-grid">
      <?php foreach($items['data'] as $w):
        $price = (float)($w['sale_price']?:$w['price']);
        $orig  = (float)$w['price'];
        $img   = !empty($w['image'])?'<img src="'.UPLOAD_URL.'/'.$w['image'].'" alt="'.e($w['name']).'">':'<span class="ph-md">🛍️</span>';
      ?>
      <div class="wish-card">
        <div class="wish-img" data-action="go" data-href="<?= APP_URL ?>/product/<?= e($w['slug']) ?>"><?= $img ?>
          <button class="wish-rm" data-action="remove-wish" data-product-id="<?= (int)$w['product_id'] ?>">✕</button>
        </div>
        <div class="wish-info">
          <div class="wish-seller">🏪 <?= e($w['shop_name']??'Shop') ?></div>
          <div class="wish-name"><?= e($w['name']) ?></div>
          <div class="wish-price"><span class="price-current"><?= currency($price) ?></span><?php if($price<$orig): ?><span class="price-orig"><?= currency($orig) ?></span><?php endif; ?></div>
          <?php if($w['status']==='active'): ?>
          <button class="btn-add-cart" data-action="add-to-cart" data-product-id="<?= (int)$w['product_id'] ?>">🛒 Add to Cart</button>
          <?php else: ?><div class="oos-note">Currently unavailable</div><?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php $scripts = '<script src="'.asset('frontend/js/account.js').'"></script>'; ?>
    </div></div></div>
