<link rel="stylesheet" href="<?= asset('frontend/css/product.css') ?>">

<div class="breadcrumb-bar">
  <div class="breadcrumb-inner">
    <a href="<?= APP_URL ?>">Home</a><span class="sep">›</span>
    <?php if(!empty($product['category_slug'])): ?><a href="<?= APP_URL ?>/category/<?= e($product['category_slug']) ?>"><?= e($product['category_name']) ?></a><span class="sep">›</span><?php endif; ?>
    <span class="current"><?= e($product['name']) ?></span>
  </div>
</div>

<div class="container" id="productPage" data-product-id="<?= (int)$product['id'] ?>" data-max-stock="<?= (int)$product['stock'] ?>">
  <div class="product-layout">
    <!-- GALLERY -->
    <div class="gallery">
      <div class="main-img-wrap">
        <?php if(!empty($product['images'])): ?>
          <img id="mainImg" class="main-img" src="<?= UPLOAD_URL.'/'.$product['images'][0]['image_path'] ?>" alt="<?= e($product['name']) ?>">
        <?php else: ?>
          <div class="main-img-placeholder">🛍️</div>
        <?php endif; ?>
        <div class="img-badges">
          <?php if($product['sale_price'] && $product['sale_price'] < $product['price']): $disc=round((1-$product['sale_price']/$product['price'])*100); ?>
          <span class="img-badge sale"><?= $disc ?>% OFF</span><?php endif; ?>
          <?php if($product['is_featured']): ?><span class="img-badge hot">⭐ FEATURED</span><?php endif; ?>
        </div>
        <button class="img-wish" id="wishBtn" data-action="toggle-wishlist" data-product-id="<?= (int)$product['id'] ?>"><?= $isWishlisted?'❤️':'🤍' ?></button>
      </div>
      <?php if(count($product['images'])>1): ?>
      <div class="thumbnails">
        <?php foreach($product['images'] as $i=>$img): ?>
        <div class="thumb <?= $i===0?'active':'' ?>" data-action="switch-img" data-src="<?= UPLOAD_URL.'/'.$img['image_path'] ?>">
          <img src="<?= UPLOAD_URL.'/'.$img['image_path'] ?>" alt="<?= e($img['alt_text'] ?? $product['name']) ?> — thumbnail <?= $i+1 ?>">
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- PRODUCT INFO -->
    <div>
      <div class="product-vendor">Sold by: <?php if(!empty($product['shop_slug'])): ?><a href="<?= APP_URL ?>/shop/<?= e($product['shop_slug']) ?>"><?= e($product['shop_name']??$product['vendor_name']) ?></a><?php else: ?><strong><?= e($product['shop_name']??$product['vendor_name']) ?></strong><?php endif; ?></div>
      <h1 class="product-main-title"><?= e($product['name']) ?></h1>

      <?php if(!empty($reviewStats['total'])): $avg=round($reviewStats['avg_rating'],1); ?>
      <div class="product-rating-row">
        <span class="stars"><?= str_repeat('★',min(5,round($avg))).str_repeat('☆',max(0,5-round($avg))) ?></span>
        <span class="rating-text"><?= $avg ?> / 5 (<a href="#reviews"><?= number_format($reviewStats['total']) ?> reviews</a>)</span>
      </div>
      <?php endif; ?>

      <div class="price-block">
        <span class="main-price"><?= currency((float)($product['sale_price']?:$product['price'])) ?></span>
        <?php if($product['sale_price'] && $product['sale_price']<$product['price']): ?>
        <span class="orig-price"><?= currency((float)$product['price']) ?></span>
        <span class="disc-badge"><?= round((1-$product['sale_price']/$product['price'])*100) ?>% off</span>
        <?php endif; ?>
      </div>
      <div class="tax-note"><?php $gstR=(float)($product['gst_rate']??18); echo $gstR>0 ? 'Inclusive of '.rtrim(rtrim(number_format($gstR,2,'.',''),'0'),'.').'% GST' : 'No GST applicable'; ?><?php if(!empty($product['hsn_code'])): ?> · HSN: <?= e($product['hsn_code']) ?><?php endif; ?></div>
      <?php if((float)($product['sale_price']?:$product['price']) >= 3000): ?>
      <div class="emi-tag">💳 EMI from <?= currency(round((float)($product['sale_price']?:$product['price'])/12)) ?>/month</div>
      <?php endif; ?>

      <div class="divider-line"></div>

      <?php if(!empty($product['variants'])): ?>
      <div class="option-label">Options</div>
      <div class="variant-btns" id="variantBtns">
        <?php foreach($product['variants'] as $v): ?>
        <button class="variant-btn" data-action="select-variant" data-variant="<?= $v['id'] ?>">
          <?= e($v['variant_name'].': '.$v['variant_value']) ?>
          <?php if($v['price_modifier']!=0): ?> (<?= $v['price_modifier']>0?'+':'' ?><?= currency(abs($v['price_modifier'])) ?>)<?php endif; ?>
        </button>
        <?php endforeach; ?>
      </div>
      <div class="divider-line"></div>
      <?php endif; ?>

      <div class="qty-row">
        <div class="option-label inline">Qty:</div>
        <div class="qty-control">
          <button class="q-btn" data-action="change-qty" data-delta="-1">−</button>
          <input class="q-num" type="number" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>">
          <button class="q-btn" data-action="change-qty" data-delta="1">+</button>
        </div>
        <?php if($product['stock']>10): ?><span class="stock-tag">✓ In Stock</span>
        <?php elseif($product['stock']>0): ?><span class="stock-tag low">⚠️ Only <?= $product['stock'] ?> left!</span>
        <?php else: ?><span class="stock-tag out">✕ Out of Stock</span><?php endif; ?>
      </div>

      <?php if($product['stock']>0): ?>
      <div class="action-btns">
        <button class="btn-cart" data-action="add-detail">🛒 Add to Cart</button>
        <button class="btn-buy" data-action="buy-now">⚡ Buy Now</button>
      </div>
      <?php else: ?>
      <button class="btn-oos" disabled>Out of Stock</button>
      <?php endif; ?>

      <div class="divider-line"></div>
      <div class="perk-grid">
        <?php foreach([['🚚','Free Delivery','above ₹499'],['🔄','Easy Return','7-day policy'],['🔒','Secure Pay','100% safe']] as [$pIc,$pTt,$pSs]): ?>
        <div class="perk-item">
          <div class="perk-icon"><?= $pIc ?></div>
          <div class="perk-title"><?= $pTt ?></div>
          <div class="perk-sub"><?= $pSs ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- TABS -->
  <div class="tabs-wrap">
    <div class="tabs-bar">
      <button class="tab-btn active" data-action="switch-tab" data-tab="tab-desc">Description</button>
      <button class="tab-btn" data-action="switch-tab" data-tab="tab-spec">Specifications</button>
      <button class="tab-btn" data-action="switch-tab" data-tab="tab-reviews">Reviews (<?= $reviewStats['total'] ?? 0 ?>)</button>
    </div>
    <div class="tab-content active" id="tab-desc">
      <div class="desc-text"><?= nl2br(e($product['description']??'No description available.')) ?></div>
    </div>
    <div class="tab-content" id="tab-spec">
      <?php $specs=[['Category',$product['category_name']??'—'],['SKU',$product['sku']??'—'],['HSN Code',$product['hsn_code']?:'—'],['GST Rate',rtrim(rtrim(number_format((float)($product['gst_rate']??18),2,'.',''),'0'),'.').'% (included in price)'],['Weight',$product['weight']?$product['weight'].' kg':'—'],['Stock',$product['stock'].' units'],['Vendor',$product['shop_name']??$product['vendor_name']]]; ?>
      <div class="spec-grid">
        <?php foreach($specs as [$k,$v]): ?><div class="spec-row"><span class="spec-key"><?= $k ?></span><span class="spec-val"><?= e($v) ?></span></div><?php endforeach; ?>
      </div>
    </div>
    <div class="tab-content" id="tab-reviews">
      <span id="reviews"></span>
      <?php if(!empty($reviews['data'])): ?>
      <div class="review-card rv-summary">
        <div class="rv-sum-flex">
          <div class="rv-sum-score"><div class="rv-score-num"><?= number_format((float)($reviewStats['avg_rating']??0),1) ?></div><div class="rv-score-stars"><?= str_repeat('★',round((float)($reviewStats['avg_rating']??0))) ?></div><div class="rv-score-count"><?= $reviewStats['total'] ?? 0 ?> reviews</div></div>
          <div class="rv-bars">
            <?php foreach([5,4,3,2,1] as $r): $cnt=$reviewStats['r'.$r]??0; $pct=$reviewStats['total']>0?round($cnt/$reviewStats['total']*100):0; ?>
            <div class="rv-bar-row">
              <span class="rv-bar-label"><?= $r ?>★</span>
              <div class="rv-bar-track"><div class="rv-bar-fill" data-pct="<?= $pct ?>"></div></div>
              <span class="rv-bar-label"><?= $cnt ?></span>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php foreach($reviews['data'] as $rv): ?>
      <div class="review-card">
        <div class="rv-head"><div class="rv-av"><?= strtoupper(substr($rv['user_name'],0,1)) ?></div><div><div class="rv-name"><?= e($rv['user_name']) ?></div><div class="rv-date"><?= formatDate($rv['created_at']) ?></div></div></div>
        <div class="rv-stars"><?= str_repeat('★',$rv['rating']).str_repeat('☆',5-$rv['rating']) ?></div>
        <?php if($rv['title']): ?><div class="rv-title"><?= e($rv['title']) ?></div><?php endif; ?>
        <div class="rv-body"><?= e($rv['body']) ?></div>
      </div>
      <?php endforeach;
      else: ?>
      <div class="rv-empty"><div class="re-icon">⭐</div><p>No reviews yet. Be the first!</p></div>
      <?php endif; ?>
    <?php if(\App\Core\Auth::isUserLoggedIn()): ?>
    <?php if($canReview ?? false): ?>
    <div class="rv-form">
      <h4 class="rv-form-title">✍️ Write a Review</h4>
      <div class="star-picker" id="starPicker">
        <?php for($i=1;$i<=5;$i++): ?><span class="sp-star" data-action="set-rating" data-star="<?= $i ?>">★</span><?php endfor; ?>
        <input type="hidden" id="ratingVal" value="5">
      </div>
      <input type="text" id="reviewTitle" class="form-input rv-title-input" placeholder="Review title (optional)">
      <textarea id="reviewBody" class="form-input form-textarea" rows="3" placeholder="Share your experience with this product..."></textarea>
      <button class="btn-cart btn-review" data-action="submit-review">Submit Review</button>
    </div>
    <?php else: ?>
    <div class="rv-note">You have already reviewed this product.</div>
    <?php endif; ?>
    <?php else: ?>
    <div class="rv-note"><a href="<?= APP_URL ?>/login">Login</a> to write a review.</div>
    <?php endif; ?>
    </div>
  </div>

  <!-- RELATED PRODUCTS -->
  <?php if(!empty($related)): ?>
  <div class="related-sec">
    <div class="related-head"><h2 class="related-title">You May Also Like</h2></div>
    <div class="related-scroll">
      <?php foreach($related as $rp):
        $rPrice=(float)($rp['sale_price']?:$rp['price']); $rOrig=(float)$rp['price'];
        $rDisc=$rOrig>0&&$rPrice<$rOrig?round((1-$rPrice/$rOrig)*100).'% off':'';
        $rImg=!empty($rp['image'])?'<img src="'.UPLOAD_URL.'/'.$rp['image'].'" alt="'.e($rp['name']).'">' : '<span class="ph-md">🛍️</span>';
      ?>
      <div class="product-card" data-action="go" data-href="<?= APP_URL ?>/product/<?= e($rp['slug']) ?>">
        <div class="product-img"><?= $rImg ?><?php if($rDisc): ?><span class="product-discount-badge"><?= $rDisc ?></span><?php endif; ?>
        <button class="product-wishlist" data-action="toggle-wishlist" data-product-id="<?= (int)$rp['id'] ?>">🤍</button></div>
        <div class="product-info"><div class="vendor-tag">🏪 <?= e($rp['shop_name']??'Shop') ?></div><div class="product-name"><?= e($rp['name']) ?></div>
        <div class="product-price"><span class="price-current"><?= currency($rPrice) ?></span><?php if($rDisc): ?><span class="price-original"><?= currency($rOrig) ?></span><?php endif; ?><span class="tax-tag">incl. taxes</span></div>
        <button class="product-add-btn" data-action="add-to-cart" data-product-id="<?= (int)$rp['id'] ?>">+ Add to Cart</button></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<?php $scripts = '<script src="'.asset('frontend/js/product.js').'"></script>'; ?>
